<?php

use api\helpers\BrowserDataParser;

require_once('View.php');

class ShortLinkView extends View
{
    function fetch()
    {
        /**
         * Если есть гет параметр utm_source
         * То храним его в куки, для аналитики оплат
         */
        if ($utm_source = $this->request->get('source_for_pay')) {
            setcookie('source_for_pay', $utm_source, 0, '/');
        }

        unset($_SESSION['restricted_mode']);
        unset($_SESSION['restricted_mode_logout_hint']);
        unset($_SESSION['user_id']);

        $params = [];

        $code = $this->request->get('link');

        $smsData = $this->orders->getLinkData((string)$code);

        if (!empty($utm_source) && $utm_source == 'vk_bot_pay')
            $this->vk_message_settings->sendLinkVisited($smsData->user_id, $code);

        if (!empty($smsData)) {
            \api\helpers\UserHelper::getJWTToken($this->config->jwt_secret_key, $smsData->user_id, 'auth_jwt_token', $this->config->jwt_expiration_time, true);

            if ($smsData->type === 'lk') {
                $this->handleLkLink($smsData);

                header('Location:/user');
                exit;
            }

            $contract = $this->contracts->get_contract_by_params(['number' => $smsData->zaim_number]);
            if ($contract && in_array($smsData->type, ['sms-payment', 'sms-prolongation', 'sms-payment-sbp'])) {
                $order = $this->orders->get_order($contract->order_id);
                if ($order && $order->status_1c != '6.Закрыт') {
                    $_SESSION['user_id'] = $smsData->user_id;
                    $_SESSION['restricted_mode'] = 1;
                }

                if ($order && $order->status_1c == '6.Закрыт') {
                    $_SESSION['user_id'] = $smsData->user_id;
                    $_SESSION['restricted_mode'] = 1;
                    $_SESSION['restricted_mode_logout_hint'] = 1;
                }

                if ($smsData->type === 'sms-prolongation') {
                    $params['is_prolongation'] = true;
                }

                if ($smsData->type === 'sms-payment-sbp' && $order) {
                    try {
                        $user = $this->users->get_user_by_id($smsData->user_id);
                        $response_balances = $this->soap->get_user_balances_array_1c($user->UID);

                        $filtered_balances = array_filter($response_balances, function ($item) use ($smsData) {
                            return $item['НомерЗайма'] == $smsData->zaim_number;
                        });

                        $balance_1c = (object)array_shift($filtered_balances);
                        $user_balance = $this->users->make_up_user_balance($smsData->user_id, $balance_1c);
                        $amount = $user_balance->ostatok_od + $user_balance->ostatok_percents + $user_balance->penalty;

                        $instalment = $balance_1c->IL != 0;

                        if ($instalment) {
                            $il_data = $this->account_contract->getIlDetail($smsData->zaim_number);
                            $fullAmount = $il_data['ОбщийДолг'] - $il_data['Баланс'];
                        } else {
                            $fullAmount = $user_balance->ostatok_od + $user_balance->ostatok_percents + $user_balance->penalty;
                        }

                        if (!empty($balance_1c->ИННТекущейОрганизации)) {
                            $organization_id = $this->organizations->get_organization_id_by_inn($balance_1c->ИННТекущейОрганизации);
                        } elseif (!empty($balance_1c->ИНН)) {
                            $organization_id = $this->organizations->get_organization_id_by_inn($balance_1c->ИНН);
                        } else {
                            $organization_id = null;
                        }

                        if (!$amount || !$organization_id) {
                            $this->logging(__METHOD__, 'short_link', $_REQUEST, [
                                'message' => 'Не удалось получить сумму платежа или id организации',
                                'context' => [
                                    'amount' => $amount,
                                    'organization_id' => $organization_id,
                                ]
                            ], 'short_link_view.txt');

                            header('Location:/user');
                            exit;
                        }

                        $action_type = $amount == $fullAmount ? $this->star_oracle::ACTION_TYPE_FULL_PAYMENT : $this->star_oracle::ACTION_TYPE_PARTIAL_PAYMENT;

                        $oracle_amount = 0;
                        if (!$instalment && $starOracle = $this->star_oracle->getStarOraclePrice($amount)) {
                            if ($amount == $fullAmount) {
                                if ($order->additional_service_so_repayment) {
                                    $oracle_amount = $starOracle->price;
                                } elseif ($order->half_additional_service_so_repayment) {
                                    $oracle_amount = round($starOracle->price / 2);
                                }
                            } elseif ($order->additional_service_so_partial_repayment) {
                                $oracle_amount = $starOracle->price;
                            } elseif ($order->half_additional_service_so_partial_repayment) {
                                $oracle_amount = round($starOracle->price / 2);
                            }
                        }

                        $tv_med_amount = 0;
                        $tv_medical = null;
                        if (!$instalment && $tv_medical = $this->tv_medical->getVItaMedPrice($amount)) {
                            if ($amount == $fullAmount) {
                                if ($order->additional_service_repayment) {
                                    $tv_med_amount = $tv_medical->price;
                                } elseif ($order->half_additional_service_repayment) {
                                    $tv_med_amount = round($tv_medical->price / 2);
                                }
                            } elseif ($order->additional_service_partial_repayment) {
                                $tv_med_amount = $tv_medical->price;
                            } elseif ($order->half_additional_service_partial_repayment) {
                                $tv_med_amount = round($tv_medical->price / 2);
                            }
                        }

                        $payment = [
                            'user_id' => $smsData->user_id,
                            'order_id' => $order->id,
                            'number' => $contract->number,
                            'card_id' => 0,
                            'amount' => $amount + $oracle_amount + $tv_med_amount,
                            'insure' => 0,
                            'multipolis' => 0,
                            'multipolis_amount' => 0,
                            'star_oracle' => (bool)$oracle_amount,
                            'star_oracle_amount' => $oracle_amount,
                            'tv_medical' => (bool)$tv_med_amount,
                            'tv_medical_id' => $tv_medical ? $tv_medical->id : 0,
                            'tv_medical_amount' => $tv_med_amount,
                            'prolongation' => 0,
                            'asp' => '',
                            'payment_type' => 'debt',
                            'calc_percents' => 0,
                            'grace_payment' => 0,
                            'organization_id' => $organization_id,
                            'chdp' => 0,
                            'pdp' => 0,
                            'payment_method' => 'sbp',
                            'create_from' => '',
                            'refinance' => 0,
                            'discount_amount' => 0,
                            'action_type' => $action_type,
                        ];

                        $payment_id = $this->best2pay->get_payment_link($payment);

                        if (!$payment_id) {
                            $this->logging(__METHOD__, 'short_link', $_REQUEST, [
                                'message' => 'Не удалось получить ссылку на оплату',
                                'context' => [
                                    'payment' => $payment,
                                ]
                            ], 'short_link_view.txt');

                            header('Location:/user');
                            exit;
                        }

                        $payment = (array) $this->best2pay->get_payment($payment_id);

                        if (!$payment || (is_array($payment) && (!isset($payment['payment_link']) || empty($payment['payment_link'])))) {
                            $this->logging(__METHOD__, 'short_link', $_REQUEST, [
                                'message' => 'Не удалось получить ссылку на оплату',
                                'context' => [
                                    'payment_id' => $payment_id,
                                    'payment' => $payment,
                                ]
                            ], 'short_link_view.txt');

                            header('Location:/user');
                            exit;
                        }

                        header('Location:' . $payment['payment_link']);
                        exit;
                    } catch (\Throwable $e) {
                        $this->logging(__METHOD__, 'short_link', $_REQUEST, [
                            'message' => 'Ошибка при получении ссылки на оплату',
                            'context' => [
                                'exception' => $e->getMessage(),
                                'code' => $e->getCode(),
                                'file' => $e->getFile(),
                                'line' => $e->getLine(),
                            ]
                        ], 'short_link_view.txt');

                        header('Location:/user');
                        exit;
                    }
                }
            }
        }

        header('Location:/user' . ($params !== [] ? '?' . http_build_query($params) : ''));
        exit;

//        if (!empty($smsData)) {
//            $user = $this->users->get_user((int)$smsData->user_id);
//            $balance = $this->getBalance($user);
//
//            $user->balance = $balance;
//            $orderId = $data->order_id ?? $this->orders->get_order_by_1c($balance->zayavka)->id;
//            $order = $this->orders->get_order($orderId);
//            $vitaMedTariffs = $this->tv_medical->getAllVitaMedPrices();
//            if ($smsData->type == 'sms-prolongation') {
//                $user->balance->calc_percents = $this->users->calc_percents($user->balance);
//                $order->multipolis_amount = $this->multipolis->getMultipolisAmount($user);
//            }
//            if ($smsData->type == 'sms-payment' || $smsData->type == '' || $smsData->type == 'sms-prolongation') {
//                $this->design->assign('user', $user);
//                $this->design->assign('balance', $balance);
//                $this->design->assign('smsData', $smsData);
//                $this->design->assign('order_data', $order);
//                $this->design->assign('tv_medical_price', $vitaMedTariffs[1]->price);
//                $this->design->assign('order_id', $orderId);
//                return $this->design->fetch('short_link.tpl');
//
//            }
//        }
    }

    private function getBalance($user)
    {
        $balance = null;
        if (!empty($user->uid) && $user->uid != 'Error') {
            $balance = $this->users->get_user_balance($user->id);
            if (!empty($this->is_developer) || strtotime($balance->last_update) < time() - 60 * 10) {
                $userBalance = $this->users->get_user_balance_1c($user->uid, true);
                $balance = new StdClass();
                $balance->ostatok_od = empty($userBalance->return->СуммаСоСкидкойОД) ?  $userBalance->return->ОстатокОД : $userBalance->return->СуммаСоСкидкойОД;
                $balance->ostatok_percents = empty($userBalance->return->СуммаСоСкидкойПроцент) ? $userBalance->return->ОстатокПроцентов : $userBalance->return->СуммаСоСкидкойПроцент;
                $balance->ostatok_peni = empty($userBalance->return->СуммаСоСкидкой) ? $userBalance->return->ОстатокПени : 0;
                $balance->penalty = empty($userBalance->return->СуммаСоСкидкой)  ? $userBalance->return->ШтрафнойКД : $userBalance->return->СуммаСоСкидкойШКД;;
                $balance->zaim_number = $userBalance->return->НомерЗайма;
                $balance->zayavka = $userBalance->return->Заявка;
                $balance->payment_date = $userBalance->return->ПланДата;
                $balance->zaim_date = $userBalance->return->ДатаЗайма;
                $balance->percent = $userBalance->return->ПроцентнаяСтавка;
                $balance->prolongation_count = $userBalance->return->КоличествоПролонгаций;
                $balance->prolongation_amount = $userBalance->return->СуммаДляПролонгации_Проценты;
                $balance->last_prolongation = $userBalance->return->ПоследняяПролонгация;
            }
        }
        return $balance;
    }

    /**
     * Вход в ЛК без ввода пароля и сохранением АСП-кода, отправленного ранее, в сессию
     *
     * @param stdClass $shortUrl
     * @return void
     */
    private function handleLkLink(stdClass $shortUrl): void
    {
        $this->logging(__METHOD__, '', $_REQUEST, $_SERVER, 'short_link_view.txt');

        if (!$this->checkIp()) {
            return;
        }

        $this->saveVisit($shortUrl);
        $isAutoConfirmEnabled = $this->settings->auto_confirm_for_auto_approve_orders_enable;

        if (empty($isAutoConfirmEnabled) || empty($shortUrl->order_id) || empty($shortUrl->user_id)) {
            return;
        }

        $orderId = (int)$shortUrl->order_id;

        if (empty($orderId)) {
            return;
        }

        $order = $this->orders->get_order($orderId);

        if ((int)$order->status !== $this->orders::STATUS_APPROVED) {
            return;
        }

        $needAutoConformOrder = $this->order_data->read($orderId, $this->order_data::NEED_AUTO_CONFIRM);

        if (empty($needAutoConformOrder)) {
            return;
        }

        $aspCodeSms = $this->getAspCodeSms($orderId);

        if (empty($aspCodeSms) || empty($aspCodeSms->created)) {
            return;
        }

        // АСП-кода действителен в течение 1 дня
        if (time() - (int)strtotime($aspCodeSms->created) > 86400) {
            return;
        }

        $aspCode = (int)$aspCodeSms->code;

        if (empty($aspCode)) {
            return;
        }

        $_SESSION['user_id'] = $shortUrl->user_id;
        $_SESSION['sms'] = $aspCode;
        $_SESSION['asp_code_already_sent'] = true;
    }

    /**
     * Сохранить переход по ссылке
     * 
     * @param stdClass $shortUrl
     * @return void
     */
    private function saveVisit(stdClass $shortUrl): void
    {
        $data = [
            'short_url_id' => $shortUrl->id,
            'ip_address' => BrowserDataParser::getIpAddress(),
            'operating_system' => BrowserDataParser::getOperatingSystem(),
            'operating_system_version' => BrowserDataParser::getOperatingSystemVersion(),
            'browser' => BrowserDataParser::getBrowser(),
            'browser_version' => BrowserDataParser::getBrowserVersion(),
            'referer_url' => BrowserDataParser::getRefererUrl(),
            'device_type' => BrowserDataParser::getDeviceType(),
            'visited_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),

        ];

        $query = $this->db->placehold("
            INSERT INTO __short_url_visits SET ?%
        ", $data);
        $this->db->query($query);
    }

    /**
     * Проверка по ip
     *
     * @return bool
     */
    private function checkIp(): bool
    {
        $blackList = [
            '66.249.83.10', // Гугл
            '66.249.83.12', // Гугл
            '66.249.83.11', // Гугл
            '149.154.161.202', // Телеграм
        ];


        if (in_array(BrowserDataParser::getIpAddress(), $blackList) || $this->isBot()) {
            return false;
        }

        return true;
    }

    private function isBot(): bool
    {
        return (
            (stripos($_SERVER['HTTP_USER_AGENT'], 'bot') !== false)
            || (stripos($_SERVER['HTTP_USER_AGENT'], 'Bot') !== false)
            || (stripos($_SERVER['HTTP_USER_AGENT'], 'developers.google.com') !== false)
            || (stripos($_SERVER['HTTP_USER_AGENT'], 'Google Favicon') !== false)
        );
    }

    /**
     * Получить смс с АСП-кодом
     *
     * @param int $orderId
     * @return stdClass|null
     */
    private function getAspCodeSms(int $orderId): ?stdClass
    {
        $sms = $this->sms->get_messages([
            'order_id' => $orderId,
            'type' => $this->sms::TYPE_ASP,
            'limit' => 1
        ]);

        if (is_array($sms)) {
            $sms = $sms[0];
        }

        return $sms ?: null;
    }
}