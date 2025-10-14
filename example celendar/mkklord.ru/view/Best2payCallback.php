<?php
require_once 'View.php';
require_once dirname(__DIR__) . '/api/addons/TVMedicalApi.php';

class Best2payCallback extends View
{

    public function fetch()
    {
        if ($this->show_unaccepted_agreement_modal() && $this->request->get('action', 'string') != 'payment')
        {
            header('Location: '.$this->config->root_url.'/user');
            exit();
        }

        switch ($this->request->get('action', 'string')):
            case 'add_card':
                $this->add_card_action();
                break;

            case 'payment':
                $this->payment_action();
                break;

            case 'payment_sbp':
                $this->payment_sbp();
                break;

            case 'sbp_token':
                $this->sbp_token();
                break;

            case 'recurrent':
                $this->recurrent();
                break;

            default:
                $meta_title = 'Ошибка';
                $this->design->assign('error', 'Ошибка');

        endswitch;

        return $this->design->fetch('best2pay_callback.tpl');
    }

    /** Колбэк после привязки */
    public function sbp_token()
    {
        $logFileName = 'sbp_tokens_callback.txt';
        $this->logging(__METHOD__, 'Best2payCallback', $_REQUEST, file_get_contents('php://input'), $logFileName);
        $subscribe = simplexml_load_string(file_get_contents('php://input'));
        if (isset($subscribe->subscription_state) && $subscribe->subscription_state == 'ACCEPTED') {

            $exist = $this->best2pay->get_sbp_account([
                'qrcId' => $subscribe->qrcId,
                'token' => $subscribe->token,
            ]);
            if (empty($exist)) {

                $transaction = $this->best2pay->get_reference_transaction($subscribe->qrcId);

                if ($transaction) {
                    $sbp_account_id = $this->best2pay->add_sbp_account([
                        'user_id' => $transaction->user_id,
                        'order_id' => $subscribe->order_id,
                        'qrcId' => $subscribe->qrcId,
                        'subscription_state' => $subscribe->subscription_state,
                        'token' => $subscribe->token,
                        'member_id' => $subscribe->member_id,
                        'signature' => $subscribe->signature,
                        'created_at' => date('Y-m-d H:i:s'),
                        'deleted' => 0,
                    ]);
                    if (!empty($sbp_account_id)) {
                        $this->best2pay->add_sbp_log([
                            'card_id' => $sbp_account_id,
                            'action' => Best2pay::CARD_ACTIONS['SUCCESS_ATTACH_SBP'],
                            'date' => date('Y-m-d H:i:s')
                        ]);
                    }
                }

            }
        }


    }

    /** Вебхук после привязки счёта СБП */
    public function payment_sbp()
    {
        $this->logging(__METHOD__, 'Best2payCallback', $_REQUEST, file_get_contents('php://input'), 'sbp_tokens.txt');

        $b2p_payment = $this->request->get('b2p_payment', 'integer');
        $user_id = $this->request->get('user_id', 'integer');
        if (empty($b2p_payment) && empty($user_id)) {
            $this->design->assign('error', 'Не передан обязательный параметр');
        }

        $payment = $this->best2pay->get_payment($b2p_payment);

        /** Что то реально пошло не так */
        if (empty($payment->id)) {
            if (!empty($user_id)) {
                $this->design->assign('success', 'Счёт успешно привязан.');
            } else {
                $this->design->assign('error', 'Счёт не привязан!');
            }
        } else {
            if ($payment->reason_code == 909) {
                $this->design->assign('error', 'Счёт не привязан!');
            } else {
                /** Привязку клиент начал делать, но колбэк sbp_token мы ещё не получили  */
                if (empty($payment->register_id)) {
                    $this->design->assign('success', 'Счёт успешно привязан!');
                }

                /** Получили колбэк sbp_token и провели оплату */
                if (!empty($payment->register_id) && !empty($payment->operation_id)) {
                    $this->design->assign('success', 'Оплата прошла успешно!');
                }
            }
        }
    }

    /**
     * @throws DateMalformedStringException
     * @throws SoapFault
     * @throws Exception
     */
    public function payment_action($type = 'pay', $data = [])
    {
        if ($type == 'sbp' && !empty($data)) {
            $register_id = (int)$data['register_id'];
            $operation = (int)$data['operation'];
            $reference = $data['reference'];
            $error = $data['error'];
            $code = $data['code'];
        } else {
            $register_id = $this->request->get('id', 'integer');
            $operation = $this->request->get('operation', 'integer');
            $reference = $this->request->get('reference', 'integer');
            $error = $this->request->get('error', 'integer');
            $code = $this->request->get('code', 'integer');
        }

        $sector = $this->best2pay->get_sector('PAYMENT');
        $asp_code = $_SESSION['sms'] ?? '';

        if (!empty($register_id)) {
            if ($payment = $this->best2pay->get_register_id_payment($register_id)) {

                if ($payment->is_sbp == 1) {
                    $sector = $this->best2pay->get_sector('AKVARIUS_PAY_CREDIT_SBP');
                }

                if ($payment->reason_code == 1) {
                    $meta_title = 'Оплата уже принята';
                    if(!empty($_SESSION['full_payment_amount'])) {
                        unset($_SESSION['full_payment_amount']);
                    }
                    $this->design->assign('error', 'Оплата уже принята.');
                } else {
                    if (empty($operation)) {
                        $register_info = $this->best2pay->get_register_info($payment->sector, $register_id);
                        $xml = simplexml_load_string($register_info);

                        foreach ($xml->operations as $xml_operation)
                            if ($xml_operation->operation->state == 'APPROVED')
                                $operation = (string)$xml_operation->operation->id;
                    }

                    if (!empty($operation)) {
                        $operation_info = $this->best2pay->get_operation_info($payment->sector, $register_id, $operation);
                        $xml = simplexml_load_string($operation_info);
                        $reason_code = (string)$xml->reason_code;
                        $payment_amount = strval($xml->amount) / 100;
                        $task_payment = $payment_amount;
                        if (!empty($xml->date)) {
                            $operation_date = date_create_from_format('Y.m.d H:i:s', (string)$xml->date);
                        }
                        if (isset($xml->type) && $xml->type == 'PURCHASE_BY_QR') {
                            $card_pan = (string) $xml->qrcId;
                            if ((string) $xml->state == 'APPROVED') {
                                $reason_code = 1;
                            }
                        } else {
                            if (!($card_pan = (string)$xml->pan)) {
                                $card_pan = (string)$xml->pan2;
                            }
                        }
                        if ($reason_code == 1) {

                            try {
                                if (!empty($card_pan)) {
                                    $this->best2pay->set_success_pay_event_in_card($card_pan, $payment->user_id);
                                }
                            } catch (Throwable $exception) {}

                            $orderId = (int) $payment->order_id;
                            $balance = $this->users->get_user_balance($payment->user_id);
                            $order = $this->orders->get_order($orderId);

                            $update = array(
                                'reason_code' => 1,
                                'operation_id' => $operation,
                                'callback_response' => serialize($operation_info),
                                'card_pan' => empty($card_pan) ? '' : $card_pan,
                                'operation_date' => $operation_date->format('Y-m-d H:i:s'),
                            );

                            $this->best2pay->update_payment($payment->id, $update);

                            // запись в базе не успевает обновиться до следующего селекта
                            sleep(2);

                            try {
                                $debt = (int) $balance->ostatok_od + (int) $balance->ostatok_percents;

                                if (
                                    $debt <= ($payment->amount + $payment->discount_amount)
                                    && !$payment->prolongation && $orderId
                                ) {
                                    $this->users->updateStatusPromoCode((int) $payment->user_id, $orderId);
                                }
                            } catch (Throwable $ex) {}

                            $meta_title = 'Оплата прошла успешно';
                            $this->design->assign('success', 'Оплата прошла успешно.');
                            $this->design->assign('grace', 'true');
                            $this->design->assign('payment_id', $payment->id);

                            if ((isset($_SESSION['restricted_mode']) && $_SESSION['restricted_mode'] == 1) ||
                                (isset($_SESSION['restricted_mode_logout_hint']) && $_SESSION['restricted_mode_logout_hint'] == 1)
                            ) {
                                if (isset($_SESSION['restricted_mode'])) {
                                    unset($_SESSION['restricted_mode']);
                                }
                                if (isset($_SESSION['user_id'])) {
                                    unset($_SESSION['user_id']);
                                }
                                if (isset($_SESSION['restricted_mode_logout_hint'])) {
                                    unset($_SESSION['restricted_mode_logout_hint']);
                                }

                                setcookie('auth_jwt_token', null, time()-1, '/');
                            }

                            $send_payment = $this->best2pay->get_payment($payment->id);

                            if($send_payment->amount == $_SESSION['full_payment_amount']) {
                                $_SESSION['full_payment_amount_done'] = true;
                            }
                            if(!empty($_SESSION['full_payment_amount'])  ) {
                                unset($_SESSION['full_payment_amount']);
                            }

                            $organization_id = $this->get_organization_id_by_payment($send_payment);
                            $send_date = date('Y-m-d H:i:s'); // Дата отправки в 1С

                            // обрабатываем оплату Кредитного рейтинга
                            if (in_array($send_payment->payment_type, array_values($this->best2pay::PAYMENT_TYPE_CREDIT_RATING_MAPPING))) {

                                // добавим задание на отправку чека
                                $receipt_data = [
                                    'user_id' => $send_payment->user_id,
                                    'order_id' => $send_payment->order_id,
                                    'amount' => $send_payment->amount,
                                    'payment_id' => $send_payment->id,
                                    'payment_method' => $this->orders::PAYMENT_METHOD_B2P,
                                    'payment_type' => $this->receipts::PAYMENT_TYPE_CREDIT_RATING,
                                    'organization_id' => $organization_id,
                                    'description' => $this->receipts::PAYMENT_DESCRIPTIONS[$this->receipts::PAYMENT_TYPE_CREDIT_RATING],
                                ];

                                $this->receipts->addItem($receipt_data);

                                $this->generateCreditRating($send_payment);
                            }

                            if ($send_payment->payment_type === $this->best2pay::PAYMENT_TYPE_REFUSER) {
                                $this->order_data->set((int)$payment->order_id, $this->order_data::PAYMENT_REFUSER, 1);
                                $this->design->assign('payment_refuser', 1);

                                // добавим задание на отправку чека
                                $receipt_data = [
                                    'user_id' => $send_payment->user_id,
                                    'order_id' => $send_payment->order_id,
                                    'amount' => $send_payment->amount,
                                    'payment_id' => $send_payment->id,
                                    'payment_method' => $this->orders::PAYMENT_METHOD_B2P,
                                    'payment_type' => $this->receipts::PAYMENT_TYPE_REFUSER,
                                    'organization_id' => $organization_id,
                                    'description' => $this->receipts::PAYMENT_DESCRIPTIONS[$this->receipts::PAYMENT_TYPE_REFUSER],
                                ];

                                $this->receipts->addItem($receipt_data);

                                $this->generateRefuserDocuments($payment, $order);

                                $result = $this->soap->send_refuser_payments([$send_payment]);

                                if (!empty($result->return) && $result->return == 'OK') {
                                    $this->best2pay->update_payment($send_payment->id, array(
                                        'sent' => 1,
                                        'send_date' => $send_date,
                                    ));
                                }
                            } else {
                                if ($send_payment->insure > 2000) {
                                    $credit_doctor_id = $this->credit_doctor->getCreditDoctorIdByPenaltyPrice(intval($send_payment->insure));

                                    // создаем ступень ШтрафногоКД
                                    $this->credit_doctor->addUserCreditDoctorData([
                                        'user_id' => $send_payment->user_id,
                                        'order_id' => $send_payment->order_id,
                                        'credit_doctor_condition_id' => $credit_doctor_id,
                                        'amount' => $send_payment->insure,
                                        'payment_method' => $this->orders::PAYMENT_METHOD_B2P,
                                        'transaction_id' => $send_payment->id,
                                        'status' => $this->credit_doctor::CREDIT_DOCTOR_STATUS_SUCCESS,
                                        'date_added' => date('Y-m-d H:i:s'),
                                        'date_edit' => date('Y-m-d H:i:s'),
                                        'is_penalty' => 1,
                                        'organization_id' => $organization_id,
                                    ]);

                                    // добавим задание на отправку чека
                                    $receipt_data = [
                                        'user_id' => $send_payment->user_id,
                                        'order_id' => $send_payment->order_id,
                                        'amount' => $send_payment->insure,
                                        'payment_id' => $send_payment->id,
                                        'payment_method' => $this->orders::PAYMENT_METHOD_B2P,
                                        'payment_type' => $this->receipts::PAYMENT_TYPE_PENALTY_CREDIT_DOCTOR,
                                        'organization_id' => $organization_id,
                                        'description' => $this->receipts::PAYMENT_DESCRIPTIONS[$this->receipts::PAYMENT_TYPE_PENALTY_CREDIT_DOCTOR],
                                    ];

                                    $this->receipts->addItem($receipt_data);
                                }
                                if ($send_payment->insure > 0) {
                                    $task_payment -= $send_payment->insure;
                                }

                                // проверим, был ли мультиполис
                                $filter_data_multipolis = [
                                    'filter_payment_id' => (int)$payment->id,
                                    'filter_payment_method' => $this->orders::PAYMENT_METHOD_B2P,
                                ];
                                $multipolis = $this->multipolis->selectAll($filter_data_multipolis, false);
                                if (!empty($multipolis)) {
                                    $send_payment->multipolis = $multipolis;

                                    // добавим задание на отправку чека
                                    $receipt_data = [
                                        'user_id' => $send_payment->user_id,
                                        'order_id' => $send_payment->order_id,
                                        'amount' => $multipolis->amount,
                                        'payment_id' => $send_payment->id,
                                        'payment_method' => $this->orders::PAYMENT_METHOD_B2P,
                                        'payment_type' => $this->receipts::PAYMENT_TYPE_MULTIPOLIS,
                                        'organization_id' => $organization_id,
                                        'description' => $this->receipts::PAYMENT_DESCRIPTIONS[$this->receipts::PAYMENT_TYPE_MULTIPOLIS],
                                    ];

                                    $this->receipts->addItem($receipt_data);

                                    $multipolis_key = $this->dop_license->createLicenseWithKey(
                                        $this->dop_license::SERVICE_CONCIERGE,
                                        [
                                            'user_id' => $send_payment->user_id,
                                            'order_id' => $send_payment->order_id,
                                            'service_id' => $multipolis->id,
                                            'organization_id' => $organization_id,
                                            'amount' => $multipolis->amount,
                                        ]
                                    );


                                    $task_payment -= $multipolis->amount;
                                }

                                // проверим была ли куплена телемедицина
                                $filter_data_tv_medical = [
                                    'filter_payment_id' => (int)$payment->id,
                                    'filter_payment_method' => $this->orders::PAYMENT_METHOD_B2P,
                                ];
                                $tv_medical_payment = $this->tv_medical->selectPayments($filter_data_tv_medical, false);
                                if (!empty($tv_medical_payment)) {
                                    $send_payment->tv_medical = $tv_medical_payment;

                                    // добавим задание на отправку чека
                                    $receipt_data = [
                                        'user_id' => $send_payment->user_id,
                                        'order_id' => $send_payment->order_id,
                                        'amount' => $tv_medical_payment->amount,
                                        'payment_id' => $send_payment->id,
                                        'payment_method' => $this->orders::PAYMENT_METHOD_B2P,
                                        'payment_type' => $this->receipts::PAYMENT_TYPE_TV_MEDICAL,
                                        'organization_id' => $organization_id,
                                        'description' => $this->receipts::PAYMENT_DESCRIPTIONS[$this->receipts::PAYMENT_TYPE_TV_MEDICAL],
                                    ];

                                    $this->receipts->addItem($receipt_data);

                                    $tvmed_key = $this->dop_license->createLicenseWithKey(
                                        $this->dop_license::SERVICE_VITAMED,
                                        [
                                            'user_id' => $send_payment->user_id,
                                            'order_id' => $send_payment->order_id,
                                            'service_id' => $tv_medical_payment->id,
                                            'organization_id' => $organization_id,
                                            'amount' => $tv_medical_payment->amount,
                                        ]
                                    );


                                    $task_payment -= $tv_medical_payment->amount;
                                }

                                // проверим была ли куплена звездный оракул
                                $filter_data_star_oracle = [
                                    'filter_transaction_id' => (int)$payment->id,
                                    'filter_payment_method' => $this->orders::PAYMENT_METHOD_B2P,
                                    'filter_action_type' => $this->star_oracle::ACTION_TYPE_PAYMENT,
                                ];
                                $star_oracle = $this->star_oracle->selectAll($filter_data_star_oracle, false);

                                if (!empty($star_oracle)) {
                                    $send_payment->star_oracle = $star_oracle;

                                    // добавим задание на отправку чека
                                    $receipt_data = [
                                        'user_id' => $send_payment->user_id,
                                        'order_id' => $send_payment->order_id,
                                        'amount' => $star_oracle->amount,
                                        'payment_id' => $send_payment->id,
                                        'payment_method' => $this->orders::PAYMENT_METHOD_B2P,
                                        'payment_type' => $this->receipts::PAYMENT_TYPE_STAR_ORACLE,
                                        'organization_id' => $organization_id,
                                        'description' => $this->receipts::PAYMENT_DESCRIPTIONS[$this->receipts::PAYMENT_TYPE_STAR_ORACLE],
                                    ];

                                    $this->receipts->addItem($receipt_data);

                                    $star_oracle_key = $this->dop_license->createLicenseWithKey(
                                        $this->dop_license::SERVICE_STAR_ORACLE,
                                        [
                                            'user_id' => $send_payment->user_id,
                                            'order_id' => $send_payment->order_id,
                                            'service_id' => $star_oracle->id,
                                            'organization_id' => $organization_id,
                                            'amount' => $star_oracle->amount,
                                        ]
                                    );


                                    $task_payment -= $star_oracle->amount;
                                }

                                if (!empty($send_payment->multipolis) || !empty($send_payment->tv_medical) || !empty($send_payment->star_oracle)) {
                                    $user = $this->users->get_user((int)$payment->user_id);
                                    $contract_number = $send_payment->contract_number ?: $balance->zaim_number;

                                    // выполним все действия по телемеду
                                    if (!empty($tv_medical_payment)) {
                                        $this->tv_medical->updatePayment(
                                            (int)$tv_medical_payment->id,
                                            ['status' => $this->tv_medical::TV_MEDICAL_PAYMENT_STATUS_SUCCESS]
                                        );

                                        $tv_medical = $this->tv_medical->getVitaMedById(
                                            (int)$tv_medical_payment->tv_medical_id
                                        );

                                        $registrationAddress = $user->Regindex . " " . $user->Regregion . " " . $user->Regcity . " " . $user->Regstreet . " " . $user->Regbuilding . " " . $user->Reghousing . " " . $user->Regroom;
                                        $actualAddress = $user->Faktindex . " " . $user->Faktregion . " " . $user->Faktcity . " " . $user->Faktstreet . " " . $user->Faktbuilding . " " . $user->Fakthousing . " " . $user->Faktroom;
                                        $passport_parts = explode("-", $user->passport_serial);
                                        $documentSerial = $passport_parts[0] ?? '';
                                        $documentNumber = $passport_parts[1] ?? '';
                                        $data_order = [
                                            'phone' => $user->phone_mobile,
                                            'firstName' => $user->firstname,
                                            'middleName' => $user->patronymic,
                                            'lastName' => $user->lastname,
                                            'birthday' => (new DateTime($user->birth))->format('Y-m-d'),
                                            //                                        'internalTariffId' => $tv_medical->api_doc_id,
                                            'sex' => strtoupper($user->gender),
                                            'documentSerial' => $documentSerial,
                                            'documentNumber' => $documentNumber,
                                            'documentIssuedBy' => $user->passport_issued,
                                            'documentDepartmentCode' => $user->subdivision_code,
                                            'registrationAddress' => $registrationAddress,
                                            'actualAddress' => $actualAddress,
                                            'email' => $user->email,
                                            'confirmCode' => $asp_code,
                                        ];
                                        $result_order = TVMedicalApi::createOrder($data_order);

                                        if (!empty($result_order['success'])) {
                                            $this->tv_medical->updatePayment(
                                                (int)$tv_medical_payment->id,
                                                ['sent_to_api' => 1]
                                            );

                                            // сгенерируем документы телемеда
                                            $this->tv_medical->generatePayDocs($user, $send_payment, (int)$tv_medical_payment->order_id, $order->organization_id,$tvmed_key);
                                        }
                                    }

                                    // выполним все действия по мультиполису
                                    if (!empty($multipolis)) {
                                        $this->multipolis->updateItem(
                                            (int)$multipolis->id,
                                            ['status' => $this->multipolis::STATUS_SUCCESS]
                                        );

                                        $clear_passport_serial = preg_replace('/[^0-9]/', '', $user->passport_serial);
                                        $passport_serial = substr($clear_passport_serial, 0, 4);
                                        $passport_number = substr($clear_passport_serial, 4);

                                        $params = [
                                            'multipolis_number' => $multipolis->number,
                                            'lastname' => $user->lastname,
                                            'firstname' => $user->firstname,
                                            'patronymic' => $user->patronymic,
                                            'birth' => $user->birth,
                                            'gender' => $user->gender,
                                            'phone_mobile' => $user->phone_mobile,
                                            'passport_serial' => $passport_serial,
                                            'passport_number' => $passport_number,
                                            'passport_original' => $user->passport_serial,
                                            'order_date_end' => $balance->payment_date,
                                            'amount' => $multipolis->amount,
                                            'pay_date' => $send_date,
                                            'payment_id' => $send_payment->id,
                                            'license_key' => $multipolis_key,
                                        ];

                                        $this->documents->create_document(
                                            [
                                                'type' => $this->documents::DOC_MULTIPOLIS,
                                                'user_id' => $multipolis->user_id,
                                                'order_id' => $multipolis->order_id,
                                                'contract_number' => $contract_number,
                                                'params' => $params,
                                                'organization_id' => $order->organization_id,
                                            ]
                                        );

                                        // отправим запрос в 1С на формирование договора по мультиполису
                                        $this->soap->sendMultipolisContract($user->uid);
                                    }

                                    // выполним все действия по звездного оракула
                                    if (!empty($star_oracle)) {
                                        $this->star_oracle->updateStarOracleData(
                                            (int)$star_oracle->id,
                                            ['status' => $this->multipolis::STATUS_SUCCESS]
                                        );


                                        $params = new StdClass();

                                        $params->lastname = $user->lastname;
                                        $params->firstname = $user->firstname;
                                        $params->patronymic = $user->patronymic;
                                        $params->birth = $user->birth;
                                        $params->passport_serial = $user->passport_serial;
                                        $params->passport_issued = $user->passport_issued;
                                        $params->passport_date = $user->passport_date;
                                        $params->subdivision_code = $user->subdivision_code;
                                        $params->phone_mobile = $user->phone_mobile;
                                        $params->accept_sms = $order->accept_sms;
                                        $params->amount = $star_oracle->amount;
                                        $params->license_key = $star_oracle_key;

                                        $this->documents->create_document(
                                            [
                                                'type' => $this->documents::CONTRACT_STAR_ORACLE,
                                                'user_id' => $order->user_id,
                                                'order_id' => $order->id,
                                                'contract_number' => $contract_number,
                                                'params' => $params,
                                                'organization_id' => $order->organization_id,
                                            ]
                                        );

                                        $this->documents->create_document(
                                            [
                                                'type' => $this->documents::STAR_ORACLE_POLICY,
                                                'user_id' => $order->user_id,
                                                'order_id' => $order->id,
                                                'contract_number' => $contract_number,
                                                'params' => $params,
                                                'organization_id' => $order->organization_id,
                                            ]
                                        );
                                    }
                                }

                                // отправляем в 1с платеж
                                $send_payment->organization = $this->organizations->get_organization($organization_id);
                                if (!empty($order) && $order->loan_type == 'IL') {
                                    $result = $this->soap->send_payments_il(array($send_payment));
                                    $this->logging(__METHOD__, 'Best2payCallback.send_payments_il', (array)$send_payment, (array)$result, 'b2p_callback.txt');
                                } else {
                                    $result = $this->soap->send_payments(array($send_payment));
                                    $this->logging(__METHOD__, 'Best2payCallback.send_payments', (array)$send_payment, (array)$result, 'b2p_callback.txt');
                                }

                                if (!empty($result->return) && $result->return == 'OK') {
                                    $this->best2pay->update_payment($send_payment->id, array(
                                        'sent' => 1,
                                        'send_date' => $send_date,
                                    ));
                                }

                                $this->updateBalance($payment, $balance);
                                $this->updateProlongations($balance, $task_payment, $payment);
                            }
                        } else {

                            try {
                                if (!empty($card_pan)) {
                                    $this->best2pay->set_error_pay_event_in_card($card_pan, $payment->user_id);
                                }
                            } catch (Throwable $exception) {}

                            $update = array(
                                'reason_code' => $reason_code,
                                'operation_id' => $operation,
                                'callback_response' => serialize($operation_info),
                                'card_pan' => empty($card_pan) ? '' : $card_pan,
                                'operation_date' => empty($operation_date) ? NULL : $operation_date->format('Y-m-d H:i:s'),
                            );

                            $this->best2pay->update_payment($payment->id, $update);

                            if (strval($xml->message) == 'Insufficient funds'){
                                $this->soap->PaymentFailed($payment->id);
                            }

                            $reason_code_description = $this->best2pay->get_reason_code_description($code);
                            $this->design->assign('reason_code_description', $reason_code_description);

                            $meta_title = 'Не удалось оплатить';
                            $this->design->assign('error', 'При оплате произошла ошибка.');
                        }
                    } else {
                        $callback_response = $this->best2pay->get_register_info($payment->sector, $register_id, $operation);
                        //echo __FILE__.' '.__LINE__.'<br /><pre>';echo(htmlspecialchars($callback_response));echo '</pre><hr />';
                        $this->best2pay->update_payment($payment->id, array(
                            'operation_id' => 0,
                            'callback_response' => serialize($callback_response)
                        ));

                        $meta_title = 'Не удалось оплатить';
                        $this->design->assign('error', 'При оплате произошла ошибка. Код ошибки: ' . $error);
                    }
                }
            } else {
                $meta_title = 'Ошибка: Оплата не найдена';
                $this->design->assign('error', 'Ошибка: Оплата не найдена');
            }
        } else {
            if(!empty($_SESSION['full_payment_amount'])) {
                unset($_SESSION['full_payment_amount']);
            }
            $meta_title = 'Ошибка запроса';
            $this->design->assign('error', 'Ошибка запроса');
        }
    }

    /**
     * Генерирует Кредитный рейтинг
     * @param $payment
     * @return void
     */
    private function generateCreditRating($payment)
    {
        $user_id = (int)$payment->user_id;
        $user = $this->users->get_user($user_id);

        $this->users->addSkipCreditRating($user_id, 'PAY');
        $this->credit_rating->handle_credit_rating_paid($user, $payment->id, $payment->asp);
        exit();
    }

    public function add_card_action()
    {
        $register_id = $this->request->get('id', 'integer');
        $operation = $this->request->get('operation', 'integer');
        $reference = $this->request->get('reference', 'integer');
        $error = $this->request->get('error', 'integer');
        $code = $this->request->get('code', 'integer');

        if (!empty($register_id)) {
            if ($transaction = $this->best2pay->get_register_id_transaction($register_id)) {
                if (empty($operation)) {
                    $register_info = $this->best2pay->get_register_info($transaction->sector, $register_id);
                    $xml = simplexml_load_string($register_info);
                    foreach ($xml->operations as $xml_operation) {
                        if ($xml_operation->operation->state == 'APPROVED') {
                            $operation = (string)$xml_operation->operation->id;
                        }
                    }
                }


                $addcard_rejected_enabled = $this->settings->addcard_rejected_enabled;
                if (empty($operation) && !empty($addcard_rejected_enabled)) {
                    foreach ($xml->operations as $xml_operation) {
                        $message = (string)$xml_operation->operation->message;
                        if ($message == 'Insufficient funds') {
                            $operation = (string)$xml_operation->operation->id;
                        }
                    }
                }

                if (!empty($operation)) {
                    $operation_info = $this->best2pay->get_operation_info($transaction->sector, $register_id, $operation);
                    $xml = simplexml_load_string($operation_info);
                    $operation_reference = (string)$xml->reference;
                    $reason_code = (string)$xml->reason_code;

                    $addcard_rejected_approve = false;
                    if (!empty($addcard_rejected_enabled)) {
                        $message = (string)$xml->message;
                        $token = (string)$xml->token;
                        if ($message == 'Insufficient funds' && !empty($token)) {
                            $addcard_rejected_approve = true;
                        }
                    }

                    if ($reason_code == 1 || !empty($addcard_rejected_approve)) {
                        $operationCorrect = true;

                        if ($crossOrder = $this->orders->get_last_order_by_status($transaction->user_id, $this->orders::STATUS_WAIT_CARD)) {
                            $crossOrderCard = $this->best2pay->get_card($crossOrder->card_id);
                            if ($crossOrder->status != $this->orders::STATUS_WAIT_CARD && $crossOrderCard->pan != $xml->pan) {
                                $request = [
                                    'crossOrderCard' => (array)$crossOrderCard,
                                    'crossOrder' => (array)$crossOrder,
                                ];
                                $response = [
                                    'xml' => (array)$xml
                                ];

                                $this->logging(__METHOD__, 'add_card_action', $request, $response, 'attach_card.txt');
                                $this->design->assign('error', 'Была привязана другая карта.');
                                $operationCorrect = false;
                            }
                        }

                        if ($operationCorrect) {
                            if ($transaction->sector == $this->best2pay->sectors['AKVARIUS_ADD_CARD']) {
                                $organization_id = $this->organizations::AKVARIUS_ID;
                            } elseif ($transaction->sector == $this->best2pay->sectors['FINLAB_ADD_CARD']) {
                                $organization_id = $this->organizations::FINLAB_ID;
                            } elseif ($transaction->sector == $this->best2pay->sectors['DEFAULT_ADD_CARD']) {
                                $organization_id = $this->config->default_organization_id;
                            } else {
                                $organization_id = $this->organizations::BOOSTRA_ID;
                            }

                            $countSameCard = $this->best2pay->find_duplicates_for_user((string)$xml->reference,(string)$xml->pan,(string)$xml->expdate, $organization_id);
                            if ($countSameCard > 0) {

                                $meta_title = 'Карта уже привязана';
                                $this->design->assign('error', 'Карта уже привязана.');
                            } else {
                                $card = array(
                                    'user_id' => (string)$transaction->user_id,
                                    'name' => (string)$xml->name,
                                    'pan' => (string)$xml->pan,
                                    'expdate' => (string)$xml->expdate,
                                    'approval_code' => (string)$xml->approval_code,
                                    'token' => (string)$xml->token,
                                    'operation_date' => str_replace('.', '-', (string)$xml->date),
                                    'created' => date('Y-m-d H:i:s'),
                                    'operation' => (string) $xml->id,
                                    'register_id' => $transaction->register_id,
                                    'transaction_id' => $transaction->id,
                                    'organization_id' => $organization_id,
                                );

                                $card_id = $this->best2pay->add_card($card);
                                if (!empty($card_id)) {
                                    $this->best2pay->add_sbp_log([
                                        'card_id' => $card_id,
                                        'action' => Best2pay::CARD_ACTIONS['ADD_CARD'],
                                        'date' => date('Y-m-d H:i:s')
                                    ]);
                                }

                                $meta_title = 'Карта успешно привязана';
                                $this->design->assign('success', '');
                                $this->design->assign('card_attach', 'true');
                                $this->design->assign('card_pan', (string)$xml->pan);
                                $this->design->assign('new_card_id', $card_id);

                                // Сохранение события доабвления новой карты
                                $this->changelogs->add_changelog(
                                    [
                                        'manager_id' => $this->managers::MANAGER_SYSTEM_ID,
                                        'created' => date('Y-m-d H:i:s'),
                                        'type' => 'new_card',
                                        'old_values' => '',
                                        'new_values' => (string)$xml->pan,
                                        'user_id' => (string)$transaction->user_id,
                                    ]
                                );

                                try {
                                    if ($this->short_flow->isShortFlowUser((string)$transaction->user_id)) {
                                        $short_flow_stage = $this->short_flow->getRegisterStage((string)$transaction->user_id);
                                        if (!empty($short_flow_stage) && $short_flow_stage == ShortRegisterView::STAGE_CARD) {
                                            $this->short_flow->setRegisterStage((string)$transaction->user_id, ShortRegisterView::STAGE_FINAL);

                                            if ($last_order = $this->orders->get_last_order((string)$transaction->user_id)) {
                                                $this->orders->update_order($last_order->id, [
                                                    'card_id' => $card_id
                                                ]);
                                            }
                                        }
                                    }
                                }
                                catch (Throwable $e) {}

                                // Если дошли сюда с кросс-ордером - ставим статус заказа на выдачу денег
                                if ($crossOrder = $this->orders->get_last_order_by_status($transaction->user_id, $this->orders::STATUS_WAIT_CARD)) {
                                    $this->orders->update_order($crossOrder->id, [
                                        'status' => $this->orders::STATUS_SIGNED
                                    ]);
                                }
                            }
                        }

                    } else {
                        $reason_code_description = $this->best2pay->get_reason_code_description($code);
                        $this->design->assign('reason_code_description', $reason_code_description);

                        $meta_title = 'Не удалось привязать карту';
                        $this->design->assign('error', 'При привязке карты произошла ошибка.');
                    }
                    $this->best2pay->update_transaction($transaction->id, array(
                        'operation' => $operation,
                        'callback_response' => $operation_info,
                        'reason_code' => $reason_code
                    ));

                    $this->best2pay->reverse((array)$transaction);
                } else {
                    $callback_response = $this->best2pay->get_register_info($transaction->sector, $register_id, $operation);
                    $this->transactions->update_transaction($transaction->id, array(
                        'operation' => 0,
                        'callback_response' => $callback_response
                    ));
                    //echo __FILE__.' '.__LINE__.'<br /><pre>';echo(htmlspecialchars($callback_response));echo '</pre><hr />';
                    $meta_title = 'Не удалось привязать карту';

                    $this->design->assign('error', 'При привязке карты произошла ошибка. Код ошибки: ' . $error);

                }
            } else {

                $meta_title = 'Ошибка: Транзакция не найдена';
                $this->design->assign('error', 'Ошибка: Транзакция не найдена');
            }
        } else {

            $meta_title = 'Ошибка запроса';
            $this->design->assign('error', 'Ошибка запроса');
        }

        //echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($_GET);echo '</pre><hr />';
    }

    /**
     * Обновление баланса клиента
     */
    private function updateBalance($payment, $balance): void
    {
        $this->logging(__METHOD__, 'Best2payCallback.updateBalance', (array)$payment, (array)$balance, 'b2p_callback.txt');

        $payUser = $this->users->get_user((int)$payment->user_id);
        $user_balance_1c = $this->users->get_user_balance_1c($payUser->uid, true);
        $user_balance_1c = $this->users->make_up_user_balance($payUser->id, $user_balance_1c->return);
        $this->logging(__METHOD__, 'Best2payCallback.updateBalance.make_up_user_balance', ['user_id' => $payment->user_id, 'zaim_number' => $payment->contract_number], (array)$user_balance_1c, 'b2p_callback.txt');

        if (empty($balance)) {
            $this->users->add_user_balance($user_balance_1c);
        } else {
            $this->users->update_user_balance($balance->id, $user_balance_1c);
        }

        $balance = $this->users->get_user_balance($payment->user_id, ['zaim_number' => $payment->contract_number]);
        $this->logging(__METHOD__, 'Best2payCallback.updateBalance.get_user_balance', ['user_id' => $payment->user_id, 'zaim_number' => $payment->contract_number], (array)$balance, 'b2p_callback.txt');
    }

    /**
     * Обновление пролонгаций после оплаты
     */
    private function updateProlongations($balance, $payment_amount, $payment): void
    {
        $task = $this->tasks->get_current_pr_task_by_balance_id($balance->id, date('Y-m-d'));

        $this->createLogFolder();
        $this->logging(__METHOD__, '//prolongations_current_task', "user_id: " . $payment->user_id, $task, 'payment_log/b2p_' . date('Y_m_d') . '.txt');

        $total_amount = !empty($balance->sum_with_grace)  ? ($balance->sum_od_with_grace+$balance->sum_percent_with_grace) : $balance->ostatok_od + $balance->ostatok_peni + $balance->ostatok_percents;
        if (!empty($balance->id) && $task) {
            $this->tasks->update_pr_task($task->id, [
                'paid' => $task->paid + $payment_amount,
                'prolongation' => $task->prolongation + $payment->prolongation,
                'close' => $payment_amount >= $total_amount ? 1 : 0,
            ]);
        }

        $contract = $this->contracts->get_contract_by_params(['number' => $payment->contract_number]);

        if ($payment_amount >= $total_amount && $contract) {
            $this->contracts->updateCloseDateInContracts($contract->number);
        }

        $task = $this->tasks->get_current_pr_task_by_balance_id($balance->id, date('Y-m-d'));
        $this->logging(__METHOD__, '//prolongations_current_task_after_update', "user_id: " . $payment->user_id, $task, 'payment_log/b2p_' . date('Y_m_d') . '.txt');
    }

    private function createLogFolder(): void
    {
        if (!is_dir($this->config->root_dir . 'logs/payment_log/')) {
            mkdir($this->config->root_dir . 'logs/payment_log/');
        }
    }

    private function get_organization_id_by_payment($payment)
    {
        $boostra_sectors = $this->best2pay->get_boostra_sectors();
        $vipzaim_sectors = $this->best2pay->get_vipzaim_sectors();
        $finlab_sectors = $this->best2pay->get_finlab_sectors();
        $default_sectors = $this->best2pay->get_default_sectors();

        if (!empty($payment->split_data)) {
            $organization_id = $this->receipts::ORGANIZATION_SPLIT_FINTEH;
        } else if (in_array($payment->sector, $default_sectors)) {
            $organization_id = $this->config->default_organization_id;
        } else if (in_array($payment->sector, $finlab_sectors)) {
            $organization_id = $this->receipts::ORGANIZATION_FINLAB;
        } else if (in_array($payment->sector, $vipzaim_sectors)) {
            $organization_id = $this->receipts::ORGANIZATION_VIPZAIM;
        } else if (in_array($payment->sector, $boostra_sectors)) {
            $organization_id = $this->receipts::ORGANIZATION_BOOSTRA;
        } else {
            $organization_id = $this->receipts::ORGANIZATION_AKVARIUS;
        }

        return $organization_id;
    }

    /**
     * @param $payment
     * @param $order
     * @return void
     */
    public function generateRefuserDocuments($payment, $order): void
    {
        $user = $this->users->get_user((int)$payment->user_id);
        $reason = $this->reasons->get_reason($order->reason_id);

        $params = [
            'lastname' => $user->lastname,
            'firstname' => $user->firstname,
            'patronymic' => $user->patronymic,
            'passport_serial' => $user->passport_serial,
            'passport_number' => $user->passport_number,
            'phone' => $user->phone_mobile,
            'fio' => $this->helpers::getFIO($user),
            'payment_id' => $payment->id,
            'text' => $reason->refusal_note,
            'date' => date('Y-m-d'),
        ];

        $this->documents->create_document(
            [
                'type' => $this->documents::PRICINA_OTKAZA_I_REKOMENDACII,
                'user_id' => $payment->user_id,
                'order_id' => $payment->order_id,
                'params' => $params,
            ]
        );
        $this->documents->create_document(
            [
                'type' => $this->documents::ZAYAVLENIYE_OTKAZA_REKOMENDACII,
                'user_id' => $payment->user_id,
                'order_id' => $payment->order_id,
                'params' => $params,
            ]
        );
        $this->documents->create_document(
            [
                'type' => $this->documents::OFFER_FAST_APPROVAL_SERVICE,
                'user_id' => $payment->user_id,
                'order_id' => $payment->order_id,
                'params' => [],
            ]
        );
    }


}
