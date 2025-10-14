<?php
error_reporting(0);
ini_set('display_errors', 'Off');

date_default_timezone_set('Europe/Moscow');

session_start();
chdir('..');

require_once 'api/Simpla.php';

$simpla = new Simpla();
$response = array();

$loan_number = $simpla->request->get('number');
$sms_code = $simpla->request->get('sms_code');
$card_id = $simpla->request->get('card_id');
$uid = $simpla->request->get('uid');
$insure_amount = 0;//$simpla->request->get('insure', 'string');
$new_nk_flow_path = $simpla->request->get('new_nk_flow_path', 'string');
$is_user_credit_doctor = $simpla->request->get('is_user_credit_doctor', 'integer');
$is_star_oracle = $simpla->request->get('is_star_oracle', 'integer');
$agree_claim_value = (int)$simpla->request->get('agree_claim_value');

$order_id = $simpla->request->get('order_id');

//$insurer = $is_user_credit_doctor == 1 ? $simpla->orders::INSURER_AL : $simpla->request->get('insurer', 'string');
$insurer = '';

if ($user = $simpla->users->get_user_by_uid($uid)) {
    if (empty($card_id)) {
        $card_resp = $simpla->notify->soap_get_card_list($uid);
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($card_resp);echo '</pre><hr />';
        foreach ($card_resp as $card_item) {
            if (empty($card_id) && $card_item->Status == 'A')
                $card_id = $card_item->CardId;
        }
    }

    $order = $simpla->orders->get_order($order_id);
    $insurer = empty($order->b2p) ? $simpla->settings->tinkoff_dop_organization : $simpla->settings->b2p_dop_organization;

    if (empty($card_id)) {
        $response['error'] = 'Нет карты для получения';
    } elseif (!empty($user->blocked)) {
        $response['error'] = 'Учетная запись заблокирована';
    } elseif ($order->accept_sms != $sms_code) {
        $response['error'] = 'Код не совпадает';

        $max_accept_try = $simpla->orders::MAX_ACCEPT_TRY;

        $accept_try = $order->accept_try + 1;
        $simpla->orders->update_order($order->id, ['accept_try' => $accept_try]);
        if ($accept_try > $max_accept_try) {
            // banned
            $simpla->users->update_user($order->user_id, ['blocked' => 1]);
            unset($_SESSION['user_id']);
        }

    } // При наличии самозапрета или отсутствии решения из акси по самозапрету
    elseif (in_array(getUserSelfDecDecision($simpla, $order), [$simpla->self_dec::DECLINE_DECISION, $simpla->self_dec::NO_DECISION])) {
        // $response обновляется внутри метода
    } else {
        $simpla->orders->update_order($order->id, array(
            'insure_amount' => $insure_amount,
            'insurer' => $insurer,
            'accept_sms' => $sms_code,
            'is_user_credit_doctor' => $is_user_credit_doctor,
        ));

        $insure_percent = round($insure_amount / $order->amount * 100, 3);

        if (!empty($simpla->is_looker))
            return false;

        $simpla->events->add_event(array(
            'user_id' => $order->user_id,
            'event' => $is_user_credit_doctor ? $simpla->events::ACCEPT_CD_ENABLED : $simpla->events::ACCEPT_CD_DISABLED,
            'created' => date('Y-m-d H:i:s'),
        ));

        if ($simpla->users->getExcessedPdn($user->id))
            $simpla->users->applyExcessedPdnNotification($user->id, $sms_code);

        //is_discount_way
        if ($new_nk_flow_path == 'green') {
            $simpla->orders->update_order($order->id, ['is_user_credit_doctor' => 1]);

            //страховка on
            $is_changed = $simpla->soap->change_order_insure_new_flow($loan_number, 1);
            if ($is_changed == "OK") {
                //$simpla->users->update_user($order->user_id, ['service_insurance' => 1]);

                //получаем обьект ордер и манагер как в срм
                $order_crm = $simpla->orders->get_crm_order($order->id);
                $manager = $simpla->managers->get_crm_manager($order_crm->manager_id);

                //green button
                if ($order_crm->percent == 0) {
                    $discount_rate = 0;
                } else {
                    $discount_rate = $order_crm->percent - ($order_crm->percent * $simpla->settings->additional_services_settings['amount_of_discount'] / 100);
                }
                if ($discount_rate < 0) {
                    $discount_rate = 0;
                }

                //обновляем процент займа в базе
                //$simpla->orders->update_order($order->id, array(
                //    'percent' => $discount_rate,
                //    'is_discount_way' => 1,
                //    'is_default_way' => 0
                //));

                //обновляем процент займа в 1с
                $green_res = $simpla->soap->update_status_1c($order_crm->id_1c, 'Одобрено', $manager->name_1c, $order_crm->amount, $discount_rate, '', 0/*кредитный доктор*/, $order_crm->period);

                //todo
            } else {
                $response['error'] = 'service_insurance_error';
                echo json_encode($response);
                exit;
            }
        }

        //is_default_way
        if ($new_nk_flow_path == 'gray') {
            $is_changed = $simpla->soap->change_order_insure_new_flow($loan_number, 0);
            if ($is_changed == "OK") {
                //страховка off
                $simpla->users->update_user($order->user_id, ['service_insurance' => 0]);
                //$simpla->orders->update_order($order->id, ['is_user_credit_doctor' => 0]);

                //новый период. Задается в настройках
                $new_period = $simpla->settings->additional_services_settings['configured_term'];
                //обновляем скрок займа в базе
                //$simpla->orders->update_order($order->id, array(
                //    'period' => $new_period,
                //    'is_default_way' => 1,
                //    'is_discount_way' => 0,
                //));

                //получаем обьект ордер и манагер как в срм
                $order_crm = $simpla->orders->get_crm_order($order->id);
                $manager = $simpla->managers->get_crm_manager($order_crm->manager_id);

                //обновляем скрок займа в 1с
                $gray_res = $simpla->soap->update_status_1c($order_crm->id_1c, 'Одобрено', $manager->name_1c, $order_crm->amount, $order_crm->percent, '', 0/*кредитный доктор*/, $new_period);

                //todo
            } else {
                $response['error'] = 'service_insurance_error';
                echo json_encode($response);
                exit;
            }

        }

        if (!empty($order->b2p)) {
            if ($simpla->is_developer) {
                $response['error'] = 'Дев режим';
                $response['is_user_credit_doctor'] = $is_user_credit_doctor;
                echo json_encode($response);
                exit;

            } elseif ($order->status == 2) {
                
                $simpla->contracts->accept_credit($order, compact(
                    'is_user_credit_doctor',
                    'is_star_oracle',
                    'agree_claim_value'
                ));
                
                // для кросс ордеров добавляем скоринги
                if ($order->utm_source == 'cross_order') {

                    $scoring_data = [
                        'user_id' => $order->user_id,
                        'order_id' => $order->id,
                        'status' => $simpla->scorings::STATUS_NEW,
                        'created' => date('Y-m-d H:i:s'),
                    ];

                    $scoring_data['type'] = $simpla->scorings::TYPE_PYTON_NBKI;
                    $simpla->scorings->add_scoring($scoring_data);

                    $scoring_data['type'] = $simpla->scorings::TYPE_PYTON_SMP;
                    $simpla->scorings->add_scoring($scoring_data);

                    if ($card = $simpla->best2pay->get_card($card_id)) {
                        // Если заявка на Финлаб
                        // А карта к финлабу не привязана - ставим новый статус
                        // И даём пользователю 10 минут на перепривязку карты
                        if ($order->organization_id == $simpla->organizations::FINLAB_ID && $card->organization_id != $simpla->organizations::FINLAB_ID) {
                            $simpla->orders->update_order($order->id, [
                                'status' => $simpla->orders::STATUS_WAIT_CARD
                            ]);

                        }
                    }
                }

            } else {
                $response['error'] = 'Заявка не находится в статусе "Одобрена"';
                echo json_encode($response);
                exit;
            }
        } elseif (empty($order->b2p)) {
            if (!$simpla->is_developer) {
                try {
                    if ($is_user_credit_doctor == 1) {
                        $credit_doctor = $simpla->credit_doctor->getCreditDoctor((int)$order->amount, $order->have_close_credits == 0);

                        $credit_doctor_data = [
                            'status' => $simpla->credit_doctor::CREDIT_DOCTOR_STATUS_NEW,
                            'user_id' => $order->user_id,
                            'order_id' => $order->id,
                            'amount' => $credit_doctor->price,
                            'credit_doctor_condition_id' => $credit_doctor->id,
                            'payment_method' => $simpla->orders::PAYMENT_METHOD_TINKOFF,
                            'organization_id' => $simpla->organizations::FINTEHMARKET_ID,
                        ];
                        $user_credit_doctor_id = $simpla->credit_doctor->addUserCreditDoctorData($credit_doctor_data);

                        // отправим информацию о КД в 1С
                        $array_soap_asp = [
                            'НомерЗаявки' => $loan_number,
                            'КодСМС' => $sms_code,
                            'CardId' => $card_id,
                            'insurer' => $insurer,
                            'СуммаКД' => $credit_doctor->price,
                            'КомплектНазвание' => 'Комплект ' . $credit_doctor->id,
                            'КомплектID' => $credit_doctor->id,
                            'inn' => empty($user->inn) ? '' : $user->inn,
                        ];
                        $object_soap = $simpla->soap->generateObject($array_soap_asp);
                        $response_soap = $simpla->soap->requestSoap($object_soap, 'WebOtvetZayavki', 'CreditRegistrationKD', 'payment.txt'); // Регистрируем КД в 1С

                        $response = [];

                        if (!empty($response_soap['response'])) {
                            $response['return'] = $response_soap['response'];
                        } else {
                            $response = $response_soap;
                        }

                        if (mb_strtolower($response_soap['response'] ?? '') !== 'ok') {
                            $simpla->credit_doctor->deleteUserCreditDoctor((int)$user_credit_doctor_id);
                            //$simpla->credit_doctor->updateUserCreditDoctorData($user_credit_doctor_id, ['status' => $simpla->credit_doctor::CREDIT_DOCTOR_STATUS_SEND]);
                        }
                    } else {
                        $response = (array)$simpla->soap->credit_registration($loan_number, $sms_code, $card_id, $insurer, $insure_percent);
                    }

                } catch (Exception $e) {
                    $response['error'] = $e->getMessage();
                }
            } else {
//                $response = (array)$simpla->soap->credit_registration($loan_number, $sms_code, $card_id, $insurer, $insure_percent);

                echo __FILE__ . ' ' . __LINE__ . '<br /><pre>';
                var_dump($response);
                echo '</pre><hr />';
            }

            $update = array(
                'pay_result' => serialize($response),
            );

            if (!empty($response['return']) && $response['return'] == 'OK') {
                $response['card_id'] = $card_id;
                $response['loan_number'] = $loan_number;
                $response['sms_code'] = $sms_code;

                //is_discount_way
                if ($new_nk_flow_path == 'green') {
                    $update['percent'] = $discount_rate;
                    $update['is_discount_way'] = 1;
                    $update['is_default_way'] = 0;
                }

                //is_default_way
                if ($new_nk_flow_path == 'gray') {
                    $update['period'] = $new_period;
                    $update['is_default_way'] = 1;
                    $update['is_discount_way'] = 0;
                }

                $update['confirm_date'] = date('Y-m-d H:i:s');
            } else {
                if (isset($order->number_of_signing_errors)) {
                    $update['number_of_signing_errors'] = $order->number_of_signing_errors + 1;
                } else {
                    $update['number_of_signing_errors'] = 1;
                }

                $response['error'] = isset($response['return']) ? $response['return'] : $response;
            }

            $simpla->orders->update_order($order->id, $update);

            if (empty($response['error'])) {
                // обновляем баланс
                sleep(1);
                $user_balance = $simpla->users->get_user_balance($order->user_id);
                $user_balance_1c = $simpla->users->get_user_balance_1c($uid, true);
                $user_balance_1c = $simpla->users->make_up_user_balance($order->user_id, $user_balance_1c->return);

                if (empty($user_balance)) {
                    $balance_id = $simpla->users->add_user_balance($user_balance_1c);
                } else {
                    $balance_id = $simpla->users->update_user_balance($user_balance->id, $user_balance_1c);
                }
            }
        } else {
            $update = array(
                'confirm_date' => date('Y-m-d H:i:s'),
                'status' => 8
            );

            $simpla->orders->update_order($order->id, $update);
        }
    }
} else {
    $response['error'] = 'undefined_user';
}

/**
 * Проверить, есть ли у пользователя самозапрет
 *
 * @param Simpla $simpla
 * @param stdClass $order
 * @return string
 */
function getUserSelfDecDecision(Simpla $simpla, stdClass $order): string {
    global $response;

    $self_dec_before_loan_issuance_enabled = $simpla->settings->self_dec_before_loan_issuance_enabled;

    if (empty($self_dec_before_loan_issuance_enabled) || $simpla->user_data->isTestUser($order->user_id)) {
        $simpla->order_data->set((int)$order->id, $simpla->order_data::SELF_DEC_DECISION, $simpla->self_dec::DISABLED_DECISION);
        return $simpla->self_dec::DISABLED_DECISION;
    }

    try {
        $selfDecDecision = $simpla->self_dec->getUserSelfDecDecision((int)$order->id);
    } catch (Throwable $e) {
        $error = [
            'Ошибка: ' . $e->getMessage(),
            'Файл: ' . $e->getFile(),
            'Строка: ' . $e->getLine(),
            'Подробности: ' . $e->getTraceAsString()
        ];
        $simpla->logging(__METHOD__, '', '', ['error' => $error], 'accept_credit.txt');
    }

    if (empty($selfDecDecision)) {
        $selfDecDecision = $simpla->self_dec::NO_DECISION;
    }

    $simpla->order_data->set((int)$order->id, $simpla->order_data::SELF_DEC_DECISION, $selfDecDecision);

    switch ($selfDecDecision) {

        // Если есть самозапрет
        case $simpla->self_dec::DECLINE_DECISION:
            $response['error'] = 'Наличие текущего самозапрета в кредитной истории. Обратитесь в поддержку';
            $response['need_reload'] = true;

            // Отказываем по заявке
            $simpla->self_dec->rejectOrder($order->id);
            break;
        case $simpla->self_dec::NO_DECISION:
            $response['error'] = 'Проверяем наличие текущего самозапрета в кредитной истории. Повторите позднее';
            $response['need_reload'] = true;
            break;
        case $simpla->self_dec::APPROVE_DECISION:
            break;
        default:
            $simpla->logging(__METHOD__, '', 'Некорректный результат проверки самозапрета', ['selfDecDecision' => $selfDecDecision], 'accept_credit.txt');
    }

    return $selfDecDecision;
}

header("Content-type: application/json; charset=UTF-8");
header("Cache-Control: must-revalidate");
header("Pragma: no-cache");
header("Expires: -1");

echo json_encode($response, JSON_UNESCAPED_UNICODE);
