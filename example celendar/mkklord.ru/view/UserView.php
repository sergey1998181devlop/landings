<?PHP

use api\asp\AspHelper;
use api\helpers\BalanceHelper;

require_once dirname(__DIR__) . '/api/addons/FinancialDoctorApi.php';
require_once('api/Scorings.php');
require_once('View.php');

class UserView extends View
{
    use \api\traits\JWTAuthTrait;

    const PAGE_ACTION_HISTORY = 'history';

    /** @var array Список первичных проверок для заявки НК или НК повторника (``have_close_credits`` = 0) */
    const SCORINGS_LIST_NK = [
        Scorings::TYPE_BLACKLIST,
        Scorings::TYPE_LOCATION,
        Scorings::TYPE_AGE,
        Scorings::TYPE_FNS,
        Scorings::TYPE_EFRSB,
        Scorings::TYPE_REPORT,
        Scorings::TYPE_JUICESCORE,
        Scorings::TYPE_LOCATION_IP,
        Scorings::TYPE_WORK,
        Scorings::TYPE_DBRAIN_PASSPORT,
    ];

    /** @var array Список первичных проверок для заявки ПК (``have_close_credits`` = 1) */
    const SCORINGS_LIST_PK = [
        Scorings::TYPE_BLACKLIST,
        Scorings::TYPE_LOCATION,
        Scorings::TYPE_AGE,
        Scorings::TYPE_FNS,
        Scorings::TYPE_EFRSB,
        Scorings::TYPE_WORK,
        Scorings::TYPE_REPORT,
        Scorings::TYPE_JUICESCORE,
    ];

    /**
     * @throws Exception
     */
    function fetch()
    {
        if (!empty($_SESSION['exitpool_completed']))
            $this->design->assign('exitpool_completed', 1);

        if (!empty($_SESSION['success_add_data'])) {
            $_SESSION['success_add_data'] = NULL;
            $this->design->assign('success_add_data', 1);
        }

        $this->jwtAuthValidate();

        if (!empty($this->user->blocked)) {
            unset($_SESSION['user_id']);
            header('Location: ' . $this->config->root_url);
            exit();
        }

        if ($this->request->get('delete_account')) {
            $this->soap->delete_user($this->user->uid);
            $this->users->update_user($this->user->id, array('enabled' => 0));

            unset($_SESSION['user_id']);

            header('Location: ' . $this->config->root_url);
            exit();
        }

        if ($this->short_flow->isShortFlowUser((int)$this->user->id)) {
            $short_flow_stage = $this->short_flow->getRegisterStage($this->user->id);
            if (!empty($short_flow_stage) && $short_flow_stage != ShortRegisterView::STAGE_FINAL) {
                header('Location: ' . $this->config->root_url . '/register');
                exit();
            }
        }

        if (empty($this->user->personal_data_added) || empty($this->user->address_data_added) || empty($this->user->additional_data_added) || (empty($this->user->files_added) && Helpers::isFilesRequired($this->user)) || empty($this->user->card_added)) {
            header('Location: ' . $this->config->root_url . '/account');
            exit();
        }

        // верификация кабутек(cyberity), перенаправляем повторно загрузить фото.
        $needPhotoVerification = $this->isNeedPhotoVerification((int)$this->user->id);
        if($needPhotoVerification) {
            header('Location: ' . $this->config->root_url . '/user/upload');
            exit();
        }

        if ($files = $this->users->get_files(array('user_id' => $this->user->id))) {
            if (count($files) > 5)
                $this->users->update_user($this->user->id, array('file_uploaded' => 1));
        }

        $hasUnacceptedAgreement = $this->show_unaccepted_agreement_modal();
        // Блокировка всех действий вне белого списка если не принято соглашение об изменении данных
        if ($hasUnacceptedAgreement)
        {
            $actions_white_list = [
                'download_credit_doctor_contract',
//                'download_credit_rating_contract',
                self::PAGE_ACTION_HISTORY
            ];
            $action = $this->request->get('action');
            if (!empty($action) && !in_array($action, $actions_white_list))
            {
                header('Location: '.$this->config->root_url.'/user');
                exit();
            }
        }

        if ($this->request->get('action') == 'download_credit_doctor_contract')
        {
            $this->credit_doctor->download_individual_contract_pdf($this->user);
            exit(0);
        }

        if ($this->request->get('action') == 'download_credit_rating_contract') {
            $this->credit_rating->download_individual_contract_pdf($this->user);
            exit(0);
        }

        if ($this->request->get('action') == 'edit_amount') {

            $order_id = $this->request->post('order_id', 'integer');
            $edit_amount = $this->request->post('edit_amount', 'integer');
            $edit_period = $this->request->post('edit_period', 'integer');

            $last_order = $this->orders->get_order($order_id);
            if (empty($last_order)) {
                $this->request->json_output(['error' => 'undefined_order']);
            }

            // изменение суммы займа доступно только для IL заявок
            if (empty($last_order->max_period) && (empty($last_order->max_amount) || $last_order->max_amount <= 30_000)) {
                $this->request->json_output(['error' => 'bad_loan_type']);
            }

            // Проверка суммы, она должна входить в разрешённый диапазон
            $amount_range = $this->orders->getAmountEditRange($last_order);
            if (empty($amount_range)) {
                $this->request->json_output(['error' => 'bad_amount']);
            }
            if ($edit_amount < $amount_range['min'] || $edit_amount > $amount_range['max']) {
                $this->request->json_output(['error' => 'bad_amount']);
            }
            
            if ($this->user->id != $last_order->user_id) {
                $this->request->json_output(['error' => 'undefined_order']);
            }
            
            if ($last_order->status != $this->orders::STATUS_APPROVED) {
                $this->request->json_output(['error' => 'fail_status']);
            }
            
            $loan_type = $this->installments->get_loan_type($edit_period);
            $this->orders->update_order($last_order->id, ['loan_type' => $loan_type]);

            $this->changelogs->add_changelog([
                'manager_id' => 0,
                'created' => date('Y-m-d H:i:s'),
                'type' => 'edit_amount',
                'old_values' => serialize(['amount' => $last_order->amount]),
                'new_values' => serialize(['amount' => $edit_amount]),
                'order_id' => $last_order->id,
                'user_id' => $last_order->user_id,
            ]);
            
            $result = $this->orders->editAmount($last_order->id_1c, (int)$last_order->id, $edit_amount, $edit_period);
            $this->request->json_output(compact('result'));
        }

        if ($this->request->get('action') == 'credit_doctor_accepted') {
            $this->credit_doctor->save_individual_contract(
                $this->user,
                $this->request->post('order_id')
            );

            return false;
        }

        if ($this->request->get('action') == 'contact_me') {
            try {
                $this->notifyApi->contactMe([
                    'external_id' => $this->user->id,
                    'uid' => $this->user->uid,
                ]);
            } catch (Exception $e) {
                $this->logging(__METHOD__, $this->config->contact_me_url, $e->getMessage(), $this->user->id, 'notify.txt');

                return json_encode([
                    'success' => false,
                    'error' => $e->getMessage(),
                ]);
            }

            echo json_encode(['success' => true]);
            exit();
        }

        if ($this->request->get('action') === 'notification_subscribe') {
            try {
                $this->notifyApi->subscribeToWebNotification([
                    'external_id' => $this->user->id,
                    'uid' => $this->user->uid,
                    'subscription' => $this->request->post('subscription'),
                ]);
            } catch (Exception $e) {
                $this->logging(__METHOD__, $this->config->web_notification_subscribe_url, $e->getMessage(), $this->user->id, 'notify.txt');

                return json_encode([
                    'success' => false,
                    'error' => $e->getMessage(),
                ]);
            }

            echo json_encode(['success' => true]);
            exit();
        }

        if ($this->request->get('action') == 'credit_rating_form_submitted') {
            $user_cards = $this->get_user_cards($this->user);

            $this->design->assign('user_approved', $this->users->getUserApprove($this->user->id));
            $this->design->assign('cards', $user_cards);
            $this->design->assign('has_default_card', $this->is_default_card_set($user_cards));
            $this->design->assign('use_b2p', $this->settings->b2p_enabled || $this->user->use_b2p);
            header("Content-type: text/html; charset=UTF-8");
            print $this->design->fetch('credit_rating/credit_rating_contract.tpl');
            exit(0);
        }

        if ($this->request->get('action') == 'credit_rating_send_sms') {
            header("Content-type: application/json; charset=UTF-8");
            echo json_encode($this->credit_rating->send_credit_rating_sms($this->user));
            exit();
        }

        if ($redirect = $this->NewLk->check_redirect($this->user)) {
            $this->design->assign('redirect', $redirect);
        }

        $need_add_fields = $this->check_need_add_fields();
        $this->design->assign('need_add_fields', $need_add_fields);

        if ($issued_loans = $this->soap1c->DebtForFIO((array)$this->user)) {
            $issued_loans = array_filter($issued_loans, function ($var) {
                return $var->ОстатокОД > 0;
            });
        }
        $this->design->assign('have_issued_loans', count($issued_loans));

        if (!$hasUnacceptedAgreement && $this->request->post('repeat_first_loan')) {
            if ($this->user->fake_order_error < 0 && $this->user->phone_mobile != '79608251384') {
                $request_service_insurance = $this->request->post('service_insurance', 'integer');
                $request_service_sms = $this->request->post('service_sms', 'integer');

                if (empty($request_service_insurance)) {
                    $this->users->update_user($this->user->id, array('fake_order_error' => $this->user->fake_order_error + 1));
                } else {
                    $this->users->update_user($this->user->id, array('fake_order_error' => 0, 'service_insurance' => 1));
                    // Отправляем в 1с запрос на обновление галочек
                    $last_order = (array)$this->orders->get_last_order($this->user->id);
                    $this->soap->change_order_services($last_order['1c_id'], $this->user->uid);
                }
                header('Location: /user');
                exit;
            }
        }

        $notsend_files = array();
        $reject_files = array();
        if ($user_files = $this->users->get_files(array('user_id' => $this->user->id, 'status' => 0))) {
            foreach ($user_files as $user_file) {
                if ($user_file->status == 0)
                    $notsend_files[] = $user_file;
                if ($user_file->status == 3)
                    $reject_files[] = $user_file;
            }
        }
        $this->design->assign('notsend_files', $notsend_files);
        $this->design->assign('reject_files', $reject_files);

        $installment_enabled = $this->installments->check_enabled($this->user);
        $this->design->assign('installment_enabled', $installment_enabled);

        $exitpool_questions = $this->exitpools->get_questions();
        $this->design->assign('exitpool_questions', $exitpool_questions);

        $this->checkAutoConfirmNewUser();

        $user_balance_1c = new stdClass();

        if (!empty($this->user->uid) && $this->user->uid != 'Error') {
            $user_balance = $this->users->get_user_balance($this->user->id);
            #echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($user_balance);echo '</pre><hr />';
            if (!empty($this->is_developer) || !empty($this->is_admin) || strtotime($user_balance->last_update) < time() - 60 * 10) {
                try {
                    $user_balance_1c = $this->users->get_user_balance_1c($this->user->uid, true);
                    $user_balance_1c = $this->users->make_up_user_balance($this->user->id, $user_balance_1c->return);

                    #echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($user_balance_1c);echo '</pre><hr />';

                    if (empty($user_balance))
                        $balance_id = $this->users->add_user_balance($user_balance_1c);
                    else
                        $balance_id = $this->users->update_user_balance($user_balance->id, $user_balance_1c);
                } catch (SoapFault $fault) {
//                    if ($this->is_developer){
                        #echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($fault);echo '</pre><hr />';
//                    }
                    throw new Exception('Не удалось получить баланс');
                    exit;
                }
            }

            if ($user_balance->buyer == 'БИКЭШ') {
                if ($this->request->get('cession') == 'shown') {
                    $this->users->update_user_balance($user_balance->id, ['is_cession_shown' => 1]);
                }

                if (!$user_balance->is_cession_shown) {
                    try {
                        $re = $this->soap->get_cession_document($user_balance->zaim_number);

                        if (empty($re)) {
                            throw new Exception('Не удалось получить документ/');
                            exit;
                        }

                        //if (isset($re->return)) {
                        $result_cession = file_put_contents("files/contracts/Cess/{$user_balance->zaim_number}.pdf", base64_decode($re->return));

                        if ($result_cession == false) {
                            throw new Exception('Не удалось получить документ.');
                            exit;
                        }
                        //}
                    } catch (SoapFault $fault) {
                        throw new Exception('Не удалось получить документ');
                        exit;
                    }
                }
            }

            $quantity_loans = json_decode($this->user->quantity_loans);
            if ($quantity_loans->КоличествоЗаймовЗаГод >= 9) {
                $quantity_loans_block = date('Y-m-d H:i:s', strtotime($quantity_loans->ДатаПервогоЗаймаЗаГод) + 86400 * 365);
                if (time() < strtotime($quantity_loans_block)) {
                    $this->design->assign('quantity_loans_block', $quantity_loans_block);
                }
            }

            $isSafetyFlow = $this->users->isSafetyFlow($this->user);
            $this->design->assign('isSafetyFlow', $isSafetyFlow);
        }

        $this->design->assign('pdn_doc', $this->users->getExcessedPdn($this->user->id));

        // Передаем в шаблон
        if (!empty($this->user->client))
            $this->design->assign('name', $this->user->client);
        $this->design->assign('email', $this->user->email);

        // для определенного списка клиентов делаем акцию на 3 дня - можно закрытть долг оплатив только ОД
        if (time() > strtotime('2021-11-17') && time() < strtotime('2021-11-20')) {
            $indulgensia_list = array_map('trim', file($this->config->root_dir . 'indulgensia.txt'));
            if (array_search($this->user->phone_mobile, $indulgensia_list) !== false)
                $this->design->assign('indulgensia', 1);
        }

        $page_action = $this->request->get('action');
        $balance = $this->users->get_user_balance($this->user->id);
        $graceAmount =  !empty($balance->sum_od_with_grace) || !empty($balance->sum_percent_with_grace);
        $this->design->assign('graceAmount', $graceAmount);
        $this->design->assign('balance', $balance);
        $busy_cards = [];
        $orders = $this->orders->get_orders([
            'user_id' => $this->user->id,
            'limit' => 1000,
        ]);
        foreach ($orders as $order) {
            if (!$order->status_1c
                || in_array($order->status_1c, $this->orders::IN_PROGRESS_STATUSES)
                || ($order->status_1c == '5.Выдан'
                    && $order->id_1c == $balance->zayavka
                    && $balance->ostatok_od + $balance->ostatok_percents + $balance->ostatok_peni > 0)) {
                $busy_cards[$order->card_id] = true;
            }
        }
        $this->design->assign('busy_cards', $busy_cards);
        if ($page_action == self::PAGE_ACTION_HISTORY) {
//				$orders = $this->orders->get_orders(array('user_id'=>$this->user->id, 'status' => array('0','1','3')));
//				$current_orders = $this->orders->get_orders(array('user_id'=>$this->user->id, 'status' => '2'));


            $this->design->assign('orders', $orders);

//			$this->design->assign('current_orders', $current_orders);
            $this->design->assign('action', self::PAGE_ACTION_HISTORY);
        } else {
            $this->design->assign('action', 'user');
        }


        if (!empty($this->user->id)) {

            /*$response = $this->soap->get_user_history($this->user->uid);
            if (!empty($response))
            {
                $last_loans = end($response);
                if ($last_loans->СтатусЗаявки === 'Отказ') {
                    $this->design->assign('redirect', 'https://kreditoff-net.ru/');
                }
            }*/

            $restricted_mode = $_SESSION['restricted_mode'] == 1;

            $prefix = 'Уважаемый';
            if ($this->user->gender == 'female') {
                $prefix = 'Уважаемая';
            }
            $this->design->assign('salute_prefix', $prefix);

            // Приветствие
            if ($restricted_mode){
                $salute = "{$this->user->firstname} {$this->user->patronymic} " . mb_substr($this->user->lastname, 0, 1) . ".";
            } else {
                $salute = "{$this->user->lastname} {$this->user->firstname} {$this->user->patronymic}";
            }

            $this->design->assign('salute', $salute);

            $amount = $this->request->post('amount', 'integer');
            $period = $this->request->post('period', 'integer');
            $card = $this->request->post('card');
            $b2p = $this->request->post('b2p', 'integer');
            $juicescore_session_id = (string)$this->request->post('juicescore_session_id');
            if (empty($juicescore_session_id) && !empty($_COOKIE['juicescore_session_id'])) {
                $juicescore_session_id = $_COOKIE['juicescore_session_id'];
            }
            $useragent = $this->request->post('juicescore_useragent') ?? $_SERVER['HTTP_USER_AGENT'];
            $local_time = (string)$this->request->post('local_time');

            $last_scorista_scoring = $this->scorings->get_last_scorista_for_user($this->user->id, true);

            //проверяем был ли куплен кредитный рейтинг
            $has_pay_credit_rating = $this->scorings->hasPayCreditRating((int)$this->user->id);
            $this->design->assign('view_score', $has_pay_credit_rating);

            //если есть пройденная скориста пишем балл в переменную
            if ($last_scorista_scoring && !empty($last_scorista_scoring->scorista_id)) {
                $score = min($last_scorista_scoring->scorista_ball, 750);

                // костыль для соответствия результатов на странице рейтинга
                //$score = $this->credit_rating->get_rating_file_number($score_min);

                $this->design->assign('score', $score);
                $this->design->assign('score_data', $this->scorings->getScoreColorAndName($score));
            }

            if ($user_discount = $this->discounts->get_active_discount($this->user->phone_mobile)) {
                $this->design->assign('user_discount', $user_discount);
            }

            $credits_history = $this->soap->get_user_credits($this->user->uid);
            $this->user->loan_history = $this->users->save_loan_history($this->user->id, $credits_history);

            $credits_history = $this->user->loan_history;

            if (empty($credits_history)) {
                $user_discount = (object)array(
                    'end_date' => null,
                    'percent' => 0,
                    'max_period' => $this->orders::MAX_PERIOD,
                );
                $this->design->assign('user_discount', $user_discount);
            }

            $last_order = (array)$this->orders->get_last_order($this->user->id);
            if (!empty($last_order)) {
                $this->orders->disabled_additional_services($last_order['id']);
            }


            if (!empty($useragent) && !empty($last_order))
            {
                $this->order_data->set(
                    $last_order['order_id'] ?? $last_order['id'],
                    $this->order_data::USERAGENT,
                    $useragent
                );
            }

            $user_data = $this->user_data->readAll($this->user->id);
            $this->design->assign('user_data', $user_data);

            $reason = $this->reasons->get_reason($last_order['reason_id']);
            //Ранее не покупал кредитный рейтинг
            // Убрал КР
            if (0 && !$has_pay_credit_rating) {
                $has_loans = array_filter((array)$orders, function ($item) {
                    return $item->confirm_date;
                });
                if (empty($last_order) //Клиент подаёт заявку первый раз
                    //Новый клиент (ранее не было закрытых договоров). Получил отказ по крайней заявке
                    || empty($has_loans) && !empty($reason)
                    //Повторный клиент (есть хотя бы один закрытый договор займа). Отказ по крайней заявке. Мораторий
                    || !($balance->ostatok_od + $balance->ostatok_percents + $balance->ostatok_peni > 0)
                    && !empty($has_loans) && !empty($reason) && $reason->maratory) {
                    $this->design->assign('show_rating_banner', 1);
                }

                $reject_statuses = [
                    $this->orders::ORDER_1C_STATUS_REJECTED,
                    $this->orders::ORDER_1C_STATUS_REJECTED_TECH,
                ];
                if ($last_order['status'] == $this->orders::STATUS_REJECTED || in_array($last_order['1c_status'], $reject_statuses)) {
                    if (empty($has_loans) || (!empty($reason) && $reason->maratory)) {
                        $this->design->assign('collapse_rating_banner', 1);
                    }
                }
            }

            // повторная заявка
            if(!empty($amount) && !$hasUnacceptedAgreement)
            {
                if (!empty($this->is_looker))
                    return false;

                if ($last_order['utm_source'] === $this->orders::UTM_RESOURCE_AUTO_APPROVE && in_array($last_order['status'], [$this->orders::STATUS_NEW, $this->orders::STATUS_APPROVED]) && !$last_order['credit_getted']) {
                    return false;
                }

                // Фикс подачи сразу нескольких заявок с разных вкладок
                if ($last_order['status'] == $this->orders::STATUS_NEW) {
                    return false;
                }

                if ($this->request->post('credit_doctor_form_submitted') !== null) {
                    $order_id = $this->credit_doctor->handle_credit_doctor_form(
                        $this->user,
                        $card,
                        $b2p,
                        $local_time
                    );

                    $this->design->assign('contract_link', '/user?action=download_credit_doctor_contract');
                    $this->design->assign('order_id', $order_id);

                    header("Content-type: text/html; charset=UTF-8");
                    print $this->design->fetch('credit_doctor/credit_doctor_contract.tpl');
                    exit(0);
                }

                $service_recurent = $this->request->post('service_recurent', 'integer');
                $service_sms = $this->request->post('service_sms', 'integer');
                $service_insurance = 0;//$this->request->post('service_insurance', 'integer');
                $service_reason = $this->request->post('service_reason', 'integer');
                $service_doctor = $this->request->post('service_doctor', 'integer');
                $is_user_credit_doctor = $this->request->post('is_user_credit_doctor', 'integer');
                $sms = $this->request->post('sms', 'string');

                // проверим пользователя на наличие условий и выключим допы
                $notOverdueLoan = \api\helpers\UserHelper::hasNotOverdueLoan($this, $this->user);
                if (!$notOverdueLoan) {
                    $is_user_credit_doctor = 0;
                }

                // формирование фейковой заявки
                $user_approved = $this->users->getUserApprove($this->user->id);

                if ($last_order['1c_status'] == '2.Отказано') {
                    if (!empty($reason) && $reason->maratory > 0) {
                        if ($reason->maratory == 999) {
                            $reason_block = 999;
                        } else {
                            if (time() < strtotime($last_order['date']) + 86400 * $reason->maratory)
                                $reason_block = date('Y-m-d H:i:s', strtotime($last_order['date']) + 86400 * $reason->maratory);
                        }
                        if (!empty($reason_block)) {
                            //тут ставим блокаду если это отказник и не отправляем дальше заявку
                            if (!$user_approved) {
                                $this->users->updateNoApprovedUserMoratorium($this->user->id);
                                header('Location: ' . $this->config->root_url . '/user');
                                exit;
                            }
                        }
                    }
                }

                $this->users->update_user($this->user->id, array(
                    'service_recurent' => $service_recurent,
                    'service_sms' => $service_sms,
                    'service_insurance' => $service_insurance,
                    'service_reason' => $service_reason,
                    'service_doctor' => $service_doctor
                ));

                $credits_history = $this->soap->get_user_credits($this->user->uid);
                $this->user->loan_history = $this->users->save_loan_history($this->user->id, $credits_history);
                $this->logging('credits_history', '', $credits_history, $this->user->loan_history, 'history.txt');

                /*if (empty($service_insurance) && $this->user->phone_mobile != '79608251384' && $this->user->phone_mobile != '79167788257')
                {
                    if (empty($quantity_loans->КоличествоЗаймовЗаГод) || $quantity_loans->КоличествоЗаймовЗаГод < 3)
                    {
                        if (empty($quantity_loans->КоличествоЗаймовЗаГод))
                        {
                            if ($this->user->fake_order_error < 100)
                            {
                                $this->users->update_user($this->user->id, array('fake_order_error' => $this->user->fake_order_error + 1));

                                $_SESSION['fake_order_amount'] = $amount;
                                $_SESSION['fake_order_period'] = $period;
                                header('Location: '.$this->config->root_url.'/user');
                                exit;
                            }

                        }
                        elseif ($this->user->fake_order_error < 2)
                        {
                            $this->users->update_user($this->user->id, array('fake_order_error' => $this->user->fake_order_error + 1));

                            $_SESSION['fake_order_amount'] = $amount;
                            $_SESSION['fake_order_period'] = $period;
                            header('Location: '.$this->config->root_url.'/user');
                            exit;
                        }
                    }

                }*/

                $percent = $this->orders::BASE_PERCENTS;
                if (empty($credits_history)) {


                    if ($period <= $this->orders::MAX_PERIOD)
                        $percent = 0;


                } else {
                    if ($user_discount = $this->discounts->get_active_discount($this->user->phone_mobile)) {
                        if ($period <= $user_discount->max_period)
                            $percent = $user_discount->percent;
                    }
                }

                $order_uid = exec($this->config->root_dir . 'generic/uidgen');

                $order = array(
                    'user_id' => $this->user->id,
                    'card_id' => $card,
                    'amount' => $amount,
                    'period' => $period,
                    'percent' => $percent,
                    'b2p' => $b2p,
                    'order_uid' => $order_uid,
                    'is_user_credit_doctor' => $is_user_credit_doctor,
                    'first_loan' => 0,
                    'date' => date('Y-m-d H:i:s'),
                    'ip' => $_SERVER['REMOTE_ADDR'],
                    'comment' => (string)$this->request->post('card'),
                    'juicescore_session_id' => $juicescore_session_id,
                    'local_time' => $local_time,

                    'utm_source' => $this->config->default_utm_source,
                    'utm_medium' => empty($_COOKIE["utm_medium"]) ? 'Site' : $_COOKIE["utm_medium"],
                    'utm_campaign' => empty($_COOKIE["utm_campaign"]) ? 'C1_main' : $_COOKIE["utm_campaign"],
                    'utm_content' => empty($_COOKIE["utm_content"]) ? '' : $_COOKIE["utm_content"],
                    'utm_term' => empty($_COOKIE["utm_term"]) ? '' : $_COOKIE["utm_term"],
                    'webmaster_id' => empty($_COOKIE["webmaster_id"]) ? '' : $_COOKIE["webmaster_id"],
                    'click_hash' => empty($_COOKIE['click_hash']) ? '' : $_COOKIE['click_hash'],

                    'autoretry' => 0,
                    'loan_type' => $this->installments->get_loan_type($period),
                    'organization_id' => $this->organizations->get_base_organization_id(['user_id' => $this->user_id]),
                );
                $order_id = $this->orders->add_order($order);
                $this->order_data->set($order_id, $this->order_data::USER_AMOUNT, $amount);
                $this->orders->saveFinkartaFp($order_id, $this->request->post('finkarta_fp'));
                $this->events->add_event(array(
                    'user_id' => $this->user->id,
                    'event' => $is_user_credit_doctor ? $this->events::ORDER_CD_ENABLED : $this->events::ORDER_CD_DISABLED,
                    'created' => date('Y-m-d H:i:s'),
                ));

                $autoretry = $this->users->check_autoretry($this->user->id, $order_id);

                if (!empty($credits_history))
                {
                    $this->orders->update_order($order_id, ['have_close_credits' => 1]);
                    $this->blocked_adv_sms->deleteItemByUserId((int)$this->user->id);
                }

                $this->logging('add_order', $order_id, $credits_history, $this->user->loan_history, 'history.txt');

                $soap_zayavka = $this->soap->send_repeat_zayavka([
                    'amount' => $amount,
                    'period' => $period,
                    'user_id' => $this->user->id,
                    'card' => $card,
                    'b2p' => $b2p,
                    'order_uid' => $order_uid,
                    'organization_id' => $order['organization_id'],
                    'utm_source' => empty($_COOKIE["utm_source"]) ? '' : $_COOKIE["utm_source"],
                    'utm_medium' => empty($_COOKIE["utm_medium"]) ? '' : $_COOKIE["utm_medium"],
                    'utm_campaign' => empty($_COOKIE["utm_campaign"]) ? '' : $_COOKIE["utm_campaign"],
                    'utm_content' => empty($_COOKIE["utm_content"]) ? '' : $_COOKIE["utm_content"],
                    'utm_term' => empty($_COOKIE["utm_term"]) ? '' : $_COOKIE["utm_term"],
                    'webmaster_id' => empty($_COOKIE["webmaster_id"]) ? '' : $_COOKIE["webmaster_id"],
                    'click_hash' => empty($_COOKIE['click_hash']) ? '' : $_COOKIE['click_hash'],
                ]);


                setcookie("utm_source", null, time() - 1, '/', 'boostra.ru');
                setcookie("utm_medium", null, time() - 1, '/', 'boostra.ru');
                setcookie("utm_campaign", null, time() - 1, '/', 'boostra.ru');
                setcookie("utm_content", null, time() - 1, '/', 'boostra.ru');
                setcookie("utm_term", null, time() - 1, '/', 'boostra.ru');
                setcookie("webmaster_id", null, time() - 1, '/', 'boostra.ru');
                setcookie("click_hash", null, time() - 1, '/', 'boostra.ru');

                if (empty($soap_zayavka->return->id_zayavka)) {
                    $this->orders->update_order($order_id, array('status' => 3, 'note' => strval($soap_zayavka->return->Error)));
                }
                else {
                    if (!empty($sms)) {
                        $this->order_data->set($order_id, $this->order_data::AUTOCONFIRM_ASP, $sms);
                    }

                    // добавляем скоринги в задание
                    $scoring_data = [
                        'user_id' => $this->user->id,
                        'order_id' => $order_id,
                        'status' => Scorings::STATUS_NEW,
                        'created' => date('Y-m-d H:i:s'),
                    ];

                    $scorings_list = empty($credits_history) ? self::SCORINGS_LIST_NK : self::SCORINGS_LIST_PK;

                    $activeScoringsType = $this->scorings->get_types(['active' => 1]);
                    $activeScoringsTypeId = array_column($activeScoringsType, 'id');

                    foreach ($scorings_list as $type) {
                        $scoring_data['type'] = $type;

                        // Если у типа скоринга выставлено active = 1, то добавляем скоринга
                        if (in_array($scoring_data['type'], $activeScoringsTypeId)) {
                            $this->scorings->add_scoring($scoring_data);
                        }
                    }

                    if (!empty($autoretry)) {
                        $this->orders->update_order($order_id, array('autoretry' => $autoretry));
//                        $scoring_data['type'] = 'scorista';
//                        $this->scorings->add_scoring($scoring_data);
                    }

                    $this->orders->update_order($order_id, array('status' => 1, '1c_id' => $soap_zayavka->return->id_zayavka));

                    $this->soap->set_order_complete($order_id);

                    //  Сохранение заявления о предоставлении микрозайма
                    foreach ([
                                 $this->documents::MICRO_ZAIM, //  Без ШКД
                                 $this->documents::MICRO_ZAIM_FULL //  С ШКД
                             ] as $document_type) {

                        $docAmount = $amount;

                        // Убираем из заявления сумму допов на опсном флоу для PDL
                        if (
                            $this->orders->isPdlOnDangerousFlow($order, $this->user) &&
                            $this->orders->isExceedingMaxLimit($order, $this->user) &&
                            $document_type === $this->documents::MICRO_ZAIM
                        ) {
                            $docAmount -= $this->orders->getAdditionalServicesPrice($order, $this->user);
                        }

                        $document_id = $this->documents->create_document([
                            'user_id' => $this->user->id,
                            'order_id' => $order_id,
                            'type' => $document_type,
                            'params' => $this->docs->getMicroZaimParamsByUser($this->user, $docAmount, $is_user_credit_doctor)
                        ]);
                        $file_url = $this->config->root_url . '/document/' . $this->user->id . '/' . $document_id;
                        $storage_uid = $this->filestorage->upload_file($file_url);
                        $this->documents->update_document($document_id, [
                            'filestorage_uid' => $storage_uid,
                        ]);
                    }


                    if (!$this->post_back->hasPostBackByOrderId((int)$order_id, 'hold')) {
                        if (in_array($order['utm_source'], $this->post_back::REPEAT_UTM_SOURCE) || empty($credits_history)) {
                            $order['id'] = $order_id;
                            $order['id_1c'] = $soap_zayavka->return->id_zayavka;
                            $this->post_back->sendNewOrder($order);
                        }
                    }
                }
                if ($order_id && isset($_SESSION['time']) && isset($_SESSION['user_ip'])) {
                    $this->users->update_loan_funnel_report($_SESSION['time'],$_SESSION['user_ip'],true,[
                        "order_request" => true,
                        'order_date' => date("Y-m-d"),
                        'order_id' => $order_id
                    ]);
                }
                if (empty($this->user->use_b2p) && empty($this->settings->b2p_enabled) && !empty($service_recurent)) {
                    $card_list = $this->notify->soap_get_card_list($this->user->uid);
                    if (!empty($card_list)) {
                        foreach ($card_list as $card) {
                            if ($card->Status == 'A') {
                                $this->soap->auto_debiting($this->user->uid, $card->CardId, 1);
                            }
                        }
                    }
                }

                $_SESSION['fake_order_amount'] = null;
                $_SESSION['fake_order_period'] = null;
                $this->users->update_user($this->user->id, array('fake_order_error' => 0));

                $this->user->loan = $order_id;
                $_SESSION['order_id'] = $order_id;




                /** постбек на лидгид за повторников
                 * if(!empty($_COOKIE['utm_source']) && $_COOKIE['utm_source'] == 'leadgid')
                 * {
                 *
                 * $leadgid_link = 'http://go.leadgid.ru/aff_lsr?offer_id=4806&adv_sub='.$order_id.'&transaction_id='.$_COOKIE["click_hash"].'&status=pending';
                 * $ch = curl_init($leadgid_link);
                 * curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                 * curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
                 * curl_setopt($ch, CURLOPT_TIMEOUT, 15);
                 * $res = curl_exec($ch);
                 * curl_close($ch);
                 * }
                 *
                 * setcookie("utm_source", null, time() - 1, '/', 'boostra.ru');
                 * setcookie("utm_medium", null, time() - 1, '/', 'boostra.ru');
                 * setcookie("utm_campaign", null, time() - 1, '/', 'boostra.ru');
                 * setcookie("utm_content", null, time() - 1, '/', 'boostra.ru');
                 * setcookie("utm_term", null, time() - 1, '/', 'boostra.ru');
                 * setcookie("webmaster_id", null, time() - 1, '/', 'boostra.ru');
                 * setcookie("click_hash", null, time() - 1, '/', 'boostra.ru');
                 */
                setcookie("checked", null, time() - 1, '/', 'boostra.ru');


                $this->design->assign('success', true);
                unset($this->request->post);

                // Отключаем дополнительные услуги
                if ($notOverdueLoan) {
                    $this->order_data->set($order_id, OrderData::ADDITIONAL_SERVICE_TV_MED, 1);
                    $this->order_data->set($order_id, OrderData::ADDITIONAL_SERVICE_MULTIPOLIS, 1);
                    $this->order_data->set($order_id, OrderData::ADDITIONAL_SERVICE_PARTIAL_REPAYMENT, 1);
                    $this->order_data->set($order_id, OrderData::ADDITIONAL_SERVICE_REPAYMENT, 1);
                }

                header('Location: ' . $this->config->root_url . '/user');
                exit;
            }

            if ($page_action == self::PAGE_ACTION_HISTORY) {
                $user_id = $this->user->id;

                $user = $this->users->get_user(intval($user_id));

                $this->design->assign('user', $user);
                $this->design->assign('action', self::PAGE_ACTION_HISTORY);

                $orders = $this->orders->get_orders(array('user_id' => $user_id));
                foreach ($orders as $order) {
                    $this->orders->update_1c_status($order);
                }
                $orders = $this->orders->get_orders(array('user_id' => $this->user->id));

                $this->design->assign('orders', $orders);

                $body = $this->design->fetch('user.tpl');

                return $body;
            } else {
                $user_id = $this->user->id;
                $user = $this->user;
                ///$user = $this->users->get_user(intval($user_id));
                setcookie("user_id", $user_id, time() + 3600, '/');

                $last_lk_visit_time = $this->users->get_user_last_lk_visit_time($user_id);
                $this->users->set_user_last_lk_visit_time($user_id);

                $user->balance = $this->users->get_user_balance(intval($user_id));

                /**
                 * Начинамм логику банеров в ЛК
                 * @var Кол-во дней просрочки $due_days
                 */

                // Если получили баланс клиента и есть дата возврата
                $response_balances = $this->soap->get_user_balances_array_1c($this->user->uid);

                $current_loan = array_filter($response_balances, function($item) use ($user) {
                    return $item['НомерЗайма'] == $user->balance->zaim_number;
                });
                if (
                    isset($current_loan) &&
                    isset($current_loan[0]) &&
                    !empty($current_loan[0]['ПланДата'])
                ) {
                    //Получаем разницу между датой возврата и текущей даты
                    $diff = date_diff(new DateTime($current_loan[0]['ПланДата']), new DateTime(date('Y-m-d 00:00:00')));
                    if ($diff->invert == 1) {
                        // Если разница вперёд добавляем минус, проще на фронте разобрать
                        if ($diff->days > 2) {
                            //Если человек не дошёл до -2 ему банер не нужен
                            $due_days = 'not';
                        } else {
                            $due_days = '-' . $diff->days;
                        }
                    } else {
                        $due_days = $diff->days;
                        if ($due_days >= 31 && $due_days <= 90) {
                            $due_days = 'not';
                        }
                        if ($due_days == 0 && $due_days != 'not') {
                            $due_days = '-1';
                        }
                    }
                } else {
                    $due_days = 'not';
                }

                if ($due_days == 0 && $due_days != 'not') {
                    $due_days = "0";
                }

                if ($user->balance->zaim_number == 'Нет открытых договоров') {
                    $due_days = 'not';
                }

                if (isset($_COOKIE['current_loan_debug']) && $_COOKIE['current_loan_debug'] === '35396e2747c17542gftRe') {
                    echo "<pre>";
                    print_r($response_balances);
                    echo "</pre>";
                }

                $this->design->assign('saler_info', [
                    'sale_info' => $current_loan[0] ? $current_loan[0]['ИнформацияОПродаже'] ?? '' : '',
                    'name' => $current_loan[0] ? $current_loan[0]['Покупатель'] ?? '' : '',
                    'phone_number' => $current_loan[0] ? $current_loan[0]['ПокупательТелефон'] ?? '' : '',
                ]);

                // Обрабатываем utm взыскания
                $this->processCollectionUtm($user, $user_balance_1c);
                #echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($testCollection);echo '</pre><hr />';


                $prolongation_available = $user->balance->prolongation_count < 5;
                // Настройки с https://manager.boostra.ru/prolongation_settings
                if ($prolongation_available) {
                    $prolongation_visible_settings = $this->settings->prolongation_visible;
                    if ($due_days != 'not' && $days = intval($due_days)) {
                        $setting = $prolongation_visible_settings[$days];
                        if (!empty($setting['enabled'])) {
                            // Скрытие баннера
                            $last_scorista = $this->scorings->get_last_scorista_for_user($user_id, true);
                            if ($last_scorista->scorista_ball >= $setting['min_ball'] && $last_scorista->scorista_ball <= $setting['max_ball']) {
                                $prolongation_available = false;
                            }
                        }
                        else {
                            // Замена текста для баннера
                            $prolongation_text_settings = $this->settings->prolongation_text;
                            $prolongation_text = $prolongation_text_settings[$days];
                            if (!empty($prolongation_text)) {
                                $this->design->assign('prolongation_text', $prolongation_text);
                            }
                        }
                    }
                }

                $this->design->assign('prolongation_amount', strval($user->balance->prolongation_amount));
                $this->design->assign('prolongation_available', $prolongation_available);
                $this->design->assign('due_days', strval($due_days));

                $today = strtotime(date('Y-m-d 00:00:00'));
                if (strtotime($user->balance->payment_date) >= $today)
                    $prolongation_insure_percent = 15;
                elseif (strtotime($user->balance->payment_date) <= ($today + 86400 * 8))
                    $prolongation_insure_percent = 25;
                else
                    $prolongation_insure_percent = 25;

                $this->design->assign('prolongation_insure_percent', $prolongation_insure_percent);

                if (!empty($_COOKIE['paypage'])) {
                    $user_balance_rest = $user->balance->ostatok_od + $user->balance->ostatok_percents + $user->balance->ostatok_peni;
                    if ($user_balance_rest == 0)
                        $this->design->assign('repeat_approve_message', 1);
                }

                $overdue = $user->balance->expired_days;

                $this->design->assign('overdue', $overdue);

                $user->balance->calc_percents = $this->users->calc_percents($user->balance);

                if ($user->balance->sale_info == 'Договор продан' && !in_array($user->balance->buyer, ['Правовая защита', 'Правовая защита ООО', 'БИКЭШ'])) {
                    $user->balance->sale_info = 'Договор перепродан';
                    $user->balance->sale_number = $user->balance->zaim_number;
                    $user->balance->zaim_number = '';
                }
                if (!empty($user->balance->zayavka)) {
                    $zaim_order = $this->orders->get_order_by_1c($user->balance->zayavka);
                    $this->design->assign('zaim_order', $zaim_order);
                }

                // блок по скорингу
                $negative_scoring = null;

                if ($scorings = $this->scorings->get_scorings(array('user_id' => $user_id))) {
                    foreach ($scorings as $scoring)
                        if (empty($scoring->success))
                            $negative_scoring = $scoring;
                }

                if (!empty($negative_scoring)) {

                    $scoring_time = strtotime($negative_scoring->created);
                    if ((time() - 43200) < $scoring_time) {
                        $next_scoring_time = $scoring_time + 43200;
                        $next_scoring_date = date('Y-m-d H:i:s', $next_scoring_time);

                        $this->design->assign('scoring_block', $next_scoring_date);
                    }

                }

                $cards = $this->get_user_cards($user);

                $cross_orders = [];
                $cross_orders_offset = 0;
                do {
                    if (empty($cross_orders_offset)) {
                        $last_order = (array)$this->orders->get_last_order($user_id);
                    } else {
                        $last_order = (array)$this->orders->get_previous_order($user_id, $cross_orders_offset);
                    }

                    if (!empty($last_order) && !empty($last_order['1c_id'])) {
                        if (!in_array($last_order['status'], [5, 8, 9, 11])) // проверить причину отказа
                        {
                            $resp = $this->orders->check_order_1c($last_order['1c_id']);

                            $stat = $resp->return->Статус;
                            $comment = $resp->return->Комментарий;
                            $official_response = $resp->return->ОфициальныйОтвет;
                            //echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($returnnnn);echo '</pre><hr />';
                            switch ($stat):

                                case 'Новая':
                                case '1.Рассматривается':
                                    $update = array(
                                        '1c_status' => $stat,
                                        'comment' => $comment,
                                    );
                                    $last_order['status_1c'] = $last_order['1c_status'] = $stat;
                                    $last_order['comment'] = $comment;
                                    break;
                                case '2.Отказано':
                                case '7.Технический отказ':
                                case 'Не определено':
                                    // todo поставить причину отказа
                                    $update = array(
                                        '1c_status' => $stat,
                                        'status' => Orders::STATUS_REJECTED,
                                        'comment' => $comment,
                                        'official_response' => $official_response,
                                        'reject_date' => date('Y-m-d H:i:s'),
                                    );
                                    $last_order['status_1c'] = $last_order['1c_status'] = $stat;
                                    $last_order['status'] = Orders::STATUS_REJECTED;
                                    $last_order['comment'] = $comment;
                                    $last_order['official_response'] = $official_response;
                                    break;

                                case '3.Одобрено':
                                case '4.Готов к выдаче':
                                    $update = array(
                                        '1c_status' => $stat,
                                        'comment' => $comment,
                                    );
                                    $last_order['status_1c'] = $last_order['1c_status'] = $stat;
                                    $last_order['comment'] = $comment;

                                    $last_order['approved_amount'] = preg_replace("/[^0-9]/", '', $resp->return->Сумма);
                                    $last_order['approved_period'] = $resp->return->ПредложениеДействуетДо;
                                    $last_order['approved_file'] = $this->documents->save_pdf($resp->return->{'ФайлBase64'}, $resp->return->{'НомерЗаявки'}, 'Preview_Contracts');
                                    $last_order['approve_max_amount'] = $last_order['max_amount'] ?: $last_order['approve_amount'] ?: $last_order['approved_amount'];

                                    $last_order = $this->orders->calculatePdlPriceOnDangerousFlow($last_order, $this->user);

                                    if (!($last_order['user_amount'] = (int)$this->order_data->read((int)$last_order['id'], $this->order_data::USER_AMOUNT))) {
                                        $last_order['user_amount'] = max(4000, $last_order['approve_max_amount'] - 1000);
                                    }
                                    break;

                                case '5.Выдан':
                                    $update = array(
                                        '1c_status' => $stat,
                                        'comment' => $comment,
                                    );
                                    $last_order['status_1c'] = $last_order['1c_status'] = $stat;
                                    $last_order['comment'] = $comment;
                                    break;

                                case '6.Закрыт':
                                    $update = array(
                                        '1c_status' => $stat,
                                        'comment' => $comment,
                                    );
                                    $last_order['status_1c'] = $last_order['1c_status'] = $stat;
                                    $last_order['comment'] = $comment;
                                    break;

                            endswitch;

                            if (!empty($update) && !$hasUnacceptedAgreement){
                                $this->orders->update_order($last_order['id'], $update);

                                if ($stat === '3.Одобрено' || $stat === '4.Готов к выдаче') {
                                    $amount_of_discount = $this->settings->additional_services_settings['amount_of_discount'];
                                    if ($last_order['percent'] == 0) {
                                        $discount_rate = 0;
                                    } else if ($last_order['utm_source'] == 'crm_auto_approve') {
                                        $discount_rate = $this->orders::BASE_PERCENTS;
                                    } else {
                                        $discount_rate = $last_order['percent'] - ($last_order['percent'] * $this->settings->additional_services_settings['amount_of_discount'] / 100);
                                    }
                                    if ($discount_rate < 0) {
                                        $discount_rate = 0;
                                    }
                                    $configured_term = $this->settings->additional_services_settings['configured_term'];

                                    $new_contracts = $this->get_new_contracts($last_order['id']);
                                    $last_order['gray_approved_file'] = $new_contracts['gray_contract'];
                                    $last_order['green_approved_file'] = $new_contracts['green_contract'];
                                    $last_order['new_approved_file'] = $new_contracts['new_contract'];

                                    $this->design->assign('amount_of_discount', $amount_of_discount);
                                    $this->design->assign('discount_rate', $discount_rate);
                                    $this->design->assign('configured_term', $configured_term);

                                    $this->design->assign('gray_approved_file', $last_order['gray_approved_file']);
                                    $this->design->assign('green_approved_file', $last_order['green_approved_file']);
                                    $this->design->assign('new_approved_file', $last_order['new_approved_file']);
                                }
                            }

                        }

                        $last_order_time = strtotime($last_order['date']);

                        $this->design->assign(
                            'first_time_visit_after_rejection',
                            $last_lk_visit_time && ($last_lk_visit_time < date('Y-m-d H:i:s', $last_order_time))
                        );

                        $user_phone = $this->user->phone_mobile ?? '';

                        /** Блок по отказу на 12 часов*/
                        if ($last_order['1c_status'] == '2.Отказано' || $last_order['status'] == 11) {
                            $reason = $this->reasons->get_reason($last_order['reason_id']);
                            if (!empty($reason) && $reason->maratory > 0) {
                                if ($reason->maratory == 999) {
                                    $reason_block = 999;
                                } else {
                                    if (time() < strtotime($last_order['date']) + 86400 * $reason->maratory)
                                        $reason_block = date('Y-m-d H:i:s', strtotime($last_order['date']) + 86400 * $reason->maratory);
                                }
                                if (!empty($reason_block) && $last_order['status'] != 11) {
                                    $is_bonon = (bool)$this->order_data->read($last_order['id'], 'is_sold_to_bonon');
                                    // Не делаем переадресацию на витрины в ЛК если клиент продан в бонон или у него отказ по самозапрету
                                    // 2 признака продажи в бонон - reason_id (старые заявки) или s_order_data
                                    if (!$is_bonon && !in_array($reason->id, [$this->reasons::REASON_CARD_SELLED_TO_BONON, $this->reasons::REASON_SELF_DEC])) {

                                        $partner_href = $this->partner_href->getActualItem((int)$last_order['have_close_credits'], 'bonon-shop-window');
                                        $this->design->assign('partner_href', $partner_href->href ? "{$partner_href->href}&p=$user_phone" : '');
                                        $this->design->assign('view_partner_href', !empty($partner_href->href));
                                        $this->partner_href->addStatistic((int)$this->user->id, (int)$partner_href->id);
                                    }

                                    // Если включен короткий флоу И продан в бонон И другой партнерской ссылки нет, то показываем ссылку на бонон
                                    if ($this->short_flow->isShortFlowUser((int)$this->user->id) && $is_bonon && empty($partner_href)) {
                                        $partner_href = $this->user_data->read((int)$this->user->id, 'rejected_nk_url');
                                        if (!empty($partner_href)) {
                                            $this->design->assign('partner_href', $partner_href);
                                            $this->design->assign('view_partner_href', true);
                                            $this->design->assign('disable_partner_href_autoredirect', true);
                                        }
                                    }

                                    $is_rejected_nk = $this->user_data->read($this->user->id, 'is_rejected_nk');
                                    $is_flow_skipped = $this->user_data->read($user_id, 'rejected_nk_skipped');

                                    // Если включен короткий флоу И НЕ продан в бонон И решения по бонону еще нет И решение о бононе должно быть,
                                    // то отправляем ajax для получения решение о бононе
                                    if ($this->short_flow->isShortFlowUser((int)$this->user->id) && !$is_bonon && $is_rejected_nk === null && $is_flow_skipped !== '1') {
                                        $this->design->assign('check_scorings_nk', true);
                                    }

                                    $this->design->assign('reason_block', $reason_block);
                                    $user->not_rating_maratorium_valid = $this->users->getNoApprovedUserNotMoratorium($this->user->id);
                                }
                            } elseif ($reason->id == $this->reasons::REASON_REMOVED_FROM_BLACKLIST) {
                                $promoCode = $this->promocodes->getLastUnusedPromoCode($user->phone_mobile);
                                
                                if ($promoCode && $promoCode->is_mandatory_issue) {
                                    $this->design->assign('next_loan_mandatory', true);
                                }
                            } elseif (time() - (43200) < $last_order_time) {
                                $next_loan_time = $last_order_time + 43200;
                                $next_loan_date = date('Y-m-d H:i:s', $next_loan_time);

                                $this->design->assign('repeat_loan_block', $next_loan_date);
                            }

                        }

                        // индивидуальное рассмотрение
                        if ($this->settings->individual_settings['enabled']) {
                            if ($individual = $this->individuals->get_individual_order($last_order['id'])) {
                                $last_order['individual'] = $individual;
                            }
                        }

                        if ($cdoctor = $this->cdoctor->get_order_item($last_order['id'])) {
                            if ($cdoctor->cdoctor_status == 'paid' || $cdoctor->cdoctor_status == 'new') {
                                $last_order['cdoctor'] = $cdoctor;
                            }
                        }

                        if (
                            $last_order['status'] == Orders::STATUS_REJECTED
                            && !in_array($last_order['reason_id'], $this->credit_doctor->get_restriction_reason_ids())
                            && !$this->credit_doctor->is_user_has_opened_doctor($this->user)
                        ) {
                            if ($this->request->get('credit_doctor')) {
                                $this->design->assign('credit_doctor_allowed', 1);
                            } else {
                                $this->design->assign('credit_doctor_banner_show', 0);
                            }
                        }

                        if (!empty($reason_block) || (time() - (86400 * 7) < $last_order_time))
                            $user->order = $last_order;

                        // если это автоодобрение получим информацию по нему
                        if ($last_order['utm_source'] == 'crm_auto_approve') {
                            $user->auto_approve_order = $this->orders->getAutoApproveOrderByOrderId((int)$last_order['id']);
                        }

                        if ($date_5days_maratorium = $this->users->check_5days_maratorium($this->user->id)) {
                            $this->design->assign('new_order_maratorium', $date_5days_maratorium);
                        }
                    }

                    if ($last_order['utm_source'] == 'cross_order') {
                        if (in_array($last_order['1c_status'], ['3.Одобрено'])) {
                            $cross_orders[] = $last_order;
                        }
                        $cross_orders_offset++;
                    }

                } while ($last_order['utm_source'] == 'cross_order');

                $hideSuccessBlock = false;

                if ($cross_orders = $this->cross_orders->update_cross_orders($cross_orders, $last_order)) {
                    if ($isAutoAcceptCrossOrders = $this->cross_orders->isAutoAccept($isSafetyFlow, $last_order)) {
                        $totalApproveAmount = $last_order['utm_source'] == 'cross_order' ? 0 : $last_order['amount'];
                        foreach ($cross_orders as $co) {
                            $totalApproveAmount += $co['amount'];
                        }
                    }

                    $hideSuccessBlock = count(array_filter($cross_orders, function ($order) {
                        return $order['status'] == $this->orders::STATUS_WAIT_CARD;
                    })) > 0;

                    $this->design->assign('totalApproveAmount', $totalApproveAmount);
                    $this->design->assign('isAutoAcceptCrossOrders', $isAutoAcceptCrossOrders);
                }

                $this->design->assign('hideSuccessBlock', $hideSuccessBlock);

                $organizations = [];
                foreach ($this->organizations->getList() as $organization) {
                    $organizations[$organization->id] = $organization->short_name;
                }
                $this->design->assign('organizations', $organizations);
                
                if ($last_order['credit_getted'] && !$cross_orders[0]['credit_getted']) {
                    $this->design->assign('cross_orders_up', 1);
                }

                if (!empty($last_order['first_loan'])) {
                    if ($this->user->fake_order_error < 100) {
                        if (empty($this->user->service_insurance)) {
                            //$this->design->assign('view_fake_first_order', 1);

                        }
                    }


                }

                if ($this->user->fake_order_error == 0) {
                    $user->not_rating_maratorium_valid = $this->users->getNoApprovedUserNotMoratorium($this->user->id);
                }

                if (empty($user->order)) {
                    if (!empty($user->balance->zayavka)) {
                        $user->order = (array)$this->orders->get_order_by_1c($user->balance->zayavka);
                    } elseif (0 && !empty($user->balance->zaim_number)) {
                        if ($contract = $this->contracts->get_contract_by_params(['number'=>$user->balance->zaim_number])) {
                            $user->order = (array)$this->orders->get_order($contract->order_id);
                        }
                    }

                }

                if (!empty($user->order)) {
                    $user->order['payment_refuser'] = $this->order_data->read($user->order['id'], $this->order_data::PAYMENT_REFUSER);
                    $user->order['is_new_card_linked'] = $this->order_data->read($user->order['id'], $this->order_data::IS_NEW_CARD_LINKED);
                }

                $this->design->assign('use_b2p', (int)($this->settings->b2p_enabled || $user->use_b2p));
//    			$this->design->assign('files', $types);
                $this->design->assign('meta_title', 'Кабинет заемщика - ' . $user->firstname . ' ' . $user->patronymic);
                $this->design->assign('action', 'user');
                $this->design->assign('user', $user);
                $this->design->assign('is_new_client', $last_order['first_loan'] ?? 0);
                $this->design->assign('user_lk_page', true);

                // мультиполис
                $multipolis_amount = $this->multipolis->getMultipolisAmount($user);
                $this->design->assign('multipolis_amount', $multipolis_amount);

                // проверим покупку КД
                $licenses = $this->credit_doctor->getLicensesByUserId($user->id);
                $creditDoctorRecords = $this->credit_doctor->getAllCreditDoctorRecordsWithReturnsByUserId($user->id);

                $filteredCreditDoctorRecords = array_filter($creditDoctorRecords, function ($record) {
                    return $record->amount_total_returned < $record->amount;
                });
                $this->design->assign('has_credit_doctor', !empty($filteredCreditDoctorRecords));

                $activeLicense = null;

                if (!empty($licenses)) {
                    foreach ($licenses as $license) {
                        $relatedService = null;
                        foreach ($creditDoctorRecords as $record) {
                            if ($record->order_id == $license->order_id) {
                                $relatedService = $record;
                                break;
                            }
                        }

                        $isAmountFullyReturned = $relatedService && $relatedService->amount_total_returned >= $relatedService->amount;

                        if ($isAmountFullyReturned) {
                            $this->credit_doctor->updateLicenseByLicenseKey($license->license_key, [
                                'active' => 0,
                            ]);
                            $license->active = 0;
                        }

                        $isLicenseEndingUnset = is_null($license->ending) || strtotime($license->ending) === strtotime('0000-00-00 00:00:00');
                        $isLicenseExpired = !$isLicenseEndingUnset && strtotime($license->ending) <= strtotime($license->created_at);

                        if (
                            $license->active &&
                            !$isLicenseExpired &&
                            !$isAmountFullyReturned
                        ) {
                            $activeLicense = $license;
                            break;
                        }
                    }
                }
                if(!empty($_SESSION['full_payment_amount_done']) && empty($this->credit_doctor->getLicenseByUserId($this->user->id))){
                    $this->design->assign('full_payment_amount_done', $_SESSION['full_payment_amount_done']);
                }
                $userGift = $this->users->getGifts($this->user->id);
                $promoGift = $this->users->getGifts((int) $_SESSION['user_id'], true);
                $payCredit = $userGift && $this->checkPayCredit($userGift);
                $promocode = null;

                if ($promoGift && !is_null($promoGift->status)) {
                    $promocode = $promoGift->promocode;
                }

                $this->design->assign('has_license', !empty($activeLicense));
                $this->design->assign('payCredit', $payCredit);
                $this->design->assign('userGift', $userGift);
                $this->design->assign('promoGift', $promoGift);
                $this->design->assign('promocode', $promocode);
                $this->design->assign('license_url', $activeLicense ? sprintf(FinancialDoctorApi::LOGIN_URL, $activeLicense->license_key) : null);

                $banners_count = count(array_filter([
                    !empty($filteredCreditDoctorRecords),
                ]));
                $this->design->assign('banners_count', $banners_count);

                //проверка на баннер с мотивацией
                $motivation_banner = $this->orders->getMotivationBannerData($last_order, $user);
                $this->design->assign('motivation_banner', $motivation_banner);

                //проверка на режим отображения промокодов
                $promo_block = $this->promocodes->promocodeMode($this->user->id);
                $this->design->assign('promo_block', $promo_block);

                /*
                if (empty($user->skip_credit_rating) && $user->additional_data_added == 1 && $last_order['status'] != 2) {
                    header('Location: ' . $this->config->root_url . '/user/credit_rating');
                    exit();
                }
                */

                $utmSource = $this->users->checkUtmSource($user_id);

                $this->design->assign('isOrganic', $utmSource);

                if ($stat == '3.Одобрено' || $stat == '4.Готов к выдаче') {
                    $showExtraService = $this->credit_doctor->isVisible($this->user->id);

                    $credit_doctor = $this->credit_doctor->getCreditDoctor((int)($user->order['amount'] ?? $last_order['amount']), empty($credits_history));
                    $this->design->assign('credit_doctor_amount', $this->credit_doctor->numberToWords($credit_doctor->price));
                    $this->design->assign('credit_doctor_amount_modal', $credit_doctor->price);
                    $this->design->assign('showExtraService', $showExtraService);
                }

                // выполним поиск, и проверим есть ли в базе разделенный займ, который не обработан до конца
                $filter_divide_order = [
                    'filter_not_statuses' => [
                        $this->orders::DIVIDE_ORDER_STATUS_CLOSED,
                        $this->orders::DIVIDE_ORDER_STATUS_ERROR,
                        $this->orders::DIVIDE_ORDER_STATUS_CLOSED_BY_ONE,
                    ],
                    'filter_user_id' => $this->user->id,
                ];

                $divide_order = $divide_pre_order_is_new = $divide_pre_order_accept_date = null;

                // Все отчеты отображаются во вьюхе для разбитых займов
                // Все отчеты берутся из 1с
                // @todo Нормализовать отображение списка всех отчетов, для всех ситуаций
                $all_orders = new StdClass();
                $all_orders->orders = [];

                // получим актуальную информацию из 1С по каждой заявке
//                $response_balances = $this->soap->get_user_balances_array_1c($this->user->uid);
                // создаем заявки для займов акадо
                foreach ($response_balances as $response_balance) {
                    $this->acado->create_order($this->user->id, $response_balance);
                }

                $organization_id = $this->users->get_organization_id($response_balances);
                $this->design->assign('organization_id', $organization_id);
                $sbp_accounts = $this->users->getSbpAccounts($this->user->id);
                if ($organization_id == $this->organizations::AKVARIUS_ID && empty($sbp_accounts) && $this->settings->sbp_enabled) {
                        $this->design->assign('sbp_attach', true);
                } else {
                    $this->design->assign('sbp_attach', false);
                }

                $previous_order = $this->orders->get_previous_order($user->id);
                // Есть автоодобренная заявка
                if (!empty($previous_order) && !empty($user->auto_approve_order)) {
                    // Были займы только в другой МКК
                    $autoapprove_other_org = !$this->organizations->is_our_card($previous_order->organization_id);
                    $this->design->assign('autoapprove_other_org', $autoapprove_other_org);

                    // Требуем перепривязку карты для принятия денег с автоодобрения, если ранее карта была привязана к другой организации
                    $autoapprove_card_reassign = false;
                    $last_order_card = null;
                    /** @var array $previous_cards ВСЕ карты клиента, даже удалённые */
                    $previous_cards = $this->best2pay->get_cards(['user_id' => $user->id]) ?: [];
                    foreach ($previous_cards as $card) {
                        if ($card->id == $previous_order->card_id) {
                            $last_order_card = $card;
                            $this->design->assign('last_order_card', $last_order_card);

                            if (!$this->organizations->is_our_card($last_order_card->organization_id)) {
                                $autoapprove_card_reassign = true;
                                $autoapprove_wrong_card = true;
                                // А тут смотрим только актуальные карты
                                foreach ($cards as $card) {
                                    if ($this->organizations->is_our_card($card->organization_id)) {
                                        //  Была привязана новая карта
                                        $autoapprove_card_reassign = false;
                                        if ($card->pan == $last_order_card->pan) {
                                            //  ВСЁ ОК, Была привязана новая карта с таким же pan
                                            $autoapprove_wrong_card = false;
                                            //  Обновляем в автозаявке карту на привязанную, если ещё не обновили
                                            if ($last_order['card_id'] != $card->id) {
                                                $this->orders->update_order($last_order['id'], [
                                                    'card_id' => $card->id
                                                ]);
                                                $last_order['card_id'] = $card->id;
                                            }
                                            break;
                                        }
                                    }
                                }
                                $this->design->assign('autoapprove_wrong_card', $autoapprove_wrong_card);
                            }
                            break;
                        }
                    }
                    if (empty($last_order_card) && $autoapprove_other_org)
                        $autoapprove_card_reassign = true;
                    $this->design->assign('autoapprove_card_reassign', $autoapprove_card_reassign);
                }

                $this->design->assign('is_user_order_taken', Helpers::isTaken($user->order));

                if (!empty($user->order)) {
                    $likezaim = $this->likezaim->check($user->order, $response_balances);
                    $this->design->assign('likezaim', $likezaim);
                }



                $loan_buyers = $this->parseLoanBuyers( $response_balances );

                $loan_buyers && $this->design->assign( 'loan_buyers', $loan_buyers );

                if ($divide_order_data = $this->orders->getDivideOrders($filter_divide_order, false)) {

                    $divide_order = new StdClass();
                    $divide_order->orders = [];

                    $divide_order->data = $divide_order_data;
                    $divide_pre_order_is_new = in_array($divide_order->data->status, $this->orders::DIVIDE_ORDER_STATUSES_IS_NEW);
                    $order = $this->orders->get_crm_order($divide_order_data->main_order_id);
                    $divide_order->orders[$order->order_uid] = new StdClass();
                    $divide_order->orders[$order->order_uid]->order = $order;
                    $divide_order->orders[$order->order_uid]->balance = $user->balance;
                }

                if (!empty($divide_order_data->divide_order_id)) {
                    $order = $this->orders->get_crm_order($divide_order_data->divide_order_id);
                    $divide_order->orders[$order->order_uid] = new StdClass();
                    $divide_order->orders[$order->order_uid]->order = $order;
                }

                foreach ($response_balances as $balance) {
                    $order_1c_id = $balance["Заявка"];
                    $order_data = new StdClass();

                    if (!empty($balance['НомерЗайма'])) {
                        if ($contract = $this->contracts->get_contract_by_params(['number'=>$balance['НомерЗайма']])) {
                            $order = $this->orders->get_crm_order($contract->order_id);
                        }
                    } elseif (!empty($order_1c_id)) {
                        $order = $this->orders->get_order_by_1c($order_1c_id);
                    } else {
                        continue;
                    }

                    $last_prolongation_payment = $this->best2pay->get_payments(
                        [
                            'order_id' => $order->id ?? $order->order_id,
                            'prolongation' => 1,
                            'reason_code' => 1
                        ],
                        false
                    );

                    $order->is_sum_hidden_after_prolongation = false;
                    if (
                        $last_prolongation_payment &&
                        strtotime($last_prolongation_payment->operation_date) > strtotime('-3 hours')
                    ) {
                        $order->is_sum_hidden_after_prolongation = true;
                    }
                    
                    if (!empty($divide_order->orders) && array_key_exists($order->order_uid, $divide_order->orders)) {
                        continue;
                    }
                    $order_data->order = $order;

                    $order_id = isset($order_data->order->id) ? $order_data->order->id : (isset($order_data->order->order_id) ? $order_data->order->order_id : null);

                    $organizationId = $this->users->getOrganizationIdByOrderId($order_id);

                    $this->design->assign('organizationId', $organizationId);

                    $all_orders->orders[$order->order_uid]= $order_data;
                }

                $set_balance = function ($order_array) use ($response_balances) {
                    $order_balance = array_filter($response_balances, function ($item) use ($order_array) {
                        return $item['Заявка'] == $order_array->order->{'id_1c'};
                    });
                    $balance_1c = (object)array_shift($order_balance);
                    $order_array->balance = $this->users->make_up_user_balance($this->user->id, $balance_1c);
                    $order_array->balance->calc_percents = $this->users->calc_percents($order_array->balance);
                    $order_array->multipolis_amount = $this->multipolis->getMultipolisAmount($order_array);

                    // проверяем возможность рефинансирования
                    if ($this->is_developer) {
                        $order_array->refinance = $this->refinance->get_refinance($order_array->balance);
                    }

                    $p2pcredits = $this->best2pay->get_p2pcredits(['order_id' => $order_array->order->order_id, 'status' => 'APPROVED'], false);
                    $order_array->balance->p2pcredits_amount = $p2pcredits ? $p2pcredits->amount : null;

                    if ($order_array->balance->loan_type == 'IL') {
                        $order_array->balance->details = $this->soap->get_il_details($order_array->balance->zaim_number);
                        $order_array->balance->need_accept = $this->installments->check_accept($order_array->balance->zaim_date);

                        $order_array->balance->details['multipolis_amount'] = 0;
                        if ($p2pcredits) {
                            $order_array->balance->details['multipolis_amount'] = (int)($order_array->balance->zaim_summ / ($order_array->balance->details['КоличествоПлатежей']) * $this->multipolis::IL_DOP_RATE * (int)$order_array->order->additional_service_multipolis);
                        }
                    }
                };
                if ($divide_order) {
                    array_walk($divide_order->orders, $set_balance);
                    $graceAmountDivide = false;
                    foreach ($divide_order->orders as $order) {
                        if (!empty($order->balance->sum_with_grace)) {
                            $graceAmountDivide = true;
                            break;
                        }
                    }
                    $this->design->assign('graceAmountDivide', $graceAmountDivide);
                }

                if ($all_orders->orders) {
                    array_walk($all_orders->orders, $set_balance);
                }

                array_walk($all_orders->orders, function(&$order) use ($user) {
                    $res = $this->best2pay->getSbpStatus($user->id, $order->order->order_id);

                    if (!empty($res) && empty($res[0]->operation_id)){
                        $order->failed_sbp = 1;
                    }
                });

                $get_documents = function ($d_order) {
                    $d_order->order = (object)$d_order->order;

                    $resp = $this->orders->check_order_1c($d_order->order->id_1c);
                    $d_order->approved_file = $this->documents->save_pdf($resp->return->{'ФайлBase64'}, $resp->return->{'НомерЗаявки'}, 'Preview_Contracts');

                    $order_id = isset($d_order->order->id) ? $d_order->order->id : (isset($d_order->order->order_id) ? $d_order->order->order_id : null);

                    $status_1c = $resp->return->Статус;
                    $this->orders->update_order($order_id, ['1c_status' => $status_1c]);
                    $d_order->status_1c = $status_1c;
                };

                // получим документы из 1С
                if ($divide_order) {
                    array_walk($divide_order->orders, $get_documents);
                }

                if ($all_orders->orders) {
                    array_walk($all_orders->orders, $get_documents);
                }

                foreach ($all_orders->orders as $order) {
                    $utc_payment_date = strtotime($order->balance->payment_date);
                    $utc_now = strtotime(date('Y-m-d 00:00:00'));

                    if ($utc_now > $utc_payment_date) {
                        $this->design->assign('loan_expired', 1);

                        // Добавим новую проверку подписи просроченного займа
                        if (!empty($order->balance->zayavka)) {
                            $status_zaim = $this->users->getZaimAspStatus($order->balance->zaim_number);
                            $hide_asp_modal = !empty($_SESSION['hide_asp_modal']) && $_SESSION['hide_asp_modal'] == $user->id;
                            $show_asp_modal = !$status_zaim && !$hide_asp_modal;
                            $this->design->assign('show_asp_modal', $show_asp_modal);

                            if (!isset($_SESSION['hide_asp_modal'])) {
                                $_SESSION['hide_asp_modal'] = $user->id;
                            }
                        }
                    }
                }


                if ($divide_order) {
                    // возьмем данные о разделении
                    $divide_pre_order = $this->orders->getDividePreOrder((int)$divide_order_data->main_order_id);
                } else {
                    // возьмем данные о разделении
                    $divide_pre_order = $this->orders->getDividePreOrder((int)$last_order['id']);
                }

                // если разделенный займ не в работе (не одобрен)
                if ($divide_pre_order_is_new || !$divide_order) {
                    $divide_pre_order_accept_date = date('d.m.Y', strtotime('+ 1 day'));
                }

                $multipolis_amount = $this->multipolis->getMultipolisAmount($user);
                $tv_medical_tariffs = $this->tv_medical->getAllTariffs();
                $tv_medical_price = $tv_medical_tariffs[0]->price;
                $tv_medical_id = $tv_medical_tariffs[0]->id;

                $user_balance = $this->users->get_user_balance($this->user->id);

                $amount = intval($user_balance->ostatok_od + $user_balance->ostatok_percents + $user_balance->ostatok_peni);

                $vitaMedPrice = $this->tv_medical->getVItaMedPrice($amount);
                $vitaMedTariffs = $this->tv_medical->getAllVitaMedPrices();

                $starOraclePrice = $this->star_oracle->getStarOraclePrice($amount);
                $star_oracle_tariffs = $this->star_oracle->getAllTariffs();

                $asp_link_params = [
                    'params' => Documents::getParamsForContractDeletedUser($this->user),
                ];

                $userId = intval($this->user->id);
                $isFirstOrderAndProlongation = $this->checkOrderAndProlongation($userId);

                if ($isFirstOrderAndProlongation) {
                    $isFirstOrderAndProlongation = 1;
                } else {
                    $isFirstOrderAndProlongation = 0;
                }

                $this->design->assign('is_first_order', $isFirstOrderAndProlongation);

                $asp_contract_delete_user_link = $this->config->root_url . '/preview/contract_delete_user_cabinet?' . http_build_query($asp_link_params);

                $akvariusExpiredDays = BalanceHelper::getDebtInDays(
                    $this->users->get_user_balance(
                        $this->user->id,
                        ['inn' => '9714011290'] // только аква
                    )
                );
                if($akvariusExpiredDays) {
                    $partner_href_expired = $this->partner_href->getActualItem((int)$last_order['have_close_credits'], 'bonon-shop-window-overdue');
                    $this->design->assign('partner_href_expired', $partner_href_expired->href ? "{$partner_href_expired->href}&p=$user_phone" : '');
                }
                $this->design->assign('akvarius_expired_days', $akvariusExpiredDays);

                // проверим пользователя на наличие условий и выключим допы
                $notOverdueLoan = \api\helpers\UserHelper::hasNotOverdueLoan($this, $this->user);
                $this->design->assign('notOverdueLoan', $notOverdueLoan);

                $this->design->assign('asp_type_remove_account', AspHelper::ASP_TYPE_CONFIRM_REMOVE_ACCOUNT);
                $this->design->assign('asp_contract_delete_user_link', $asp_contract_delete_user_link);
                $this->design->assign('vita_med', $vitaMedPrice);
                $this->design->assign('star_oracle', $starOraclePrice);
                $this->design->assign('tv_medical_price', $vitaMedTariffs[1]->price);
                $this->design->assign('multipolis_amount', $multipolis_amount);
                $this->design->assign('tv_medical_id', $vitaMedTariffs[1]->id);
                $this->design->assign('vita_med_tariffs', $vitaMedTariffs);
                $this->design->assign('tv_medical_tariffs', $tv_medical_tariffs);
                $this->design->assign('star_oracle_tariffs', $star_oracle_tariffs);
                $this->design->assign('divide_order', $divide_order);
                $this->design->assign('all_orders', $all_orders);
                $this->design->assign('divide_pre_order', $divide_pre_order);
                $this->design->assign('divide_pre_order_is_new', $divide_pre_order_is_new);
                $this->design->assign('divide_pre_order_accept_date', $divide_pre_order_accept_date);

                $this->design->assign('user_return_credit_doctor', (int)($this->users->getUserReturnExtraService($this->user->id,'credit_doctor') > 3));
                $this->design->assign('applied_promocode', !empty($last_order['promocode']) ? $this->promocodes->getInfoById($last_order['promocode']) : null);
                $this->design->assign('last_order', $last_order);
                $isLastOrderAutoApproved = $last_order['utm_source'] === $this->orders::UTM_RESOURCE_AUTO_APPROVE;
                $this->design->assign('is_last_order_auto_approved', $isLastOrderAutoApproved);
                $this->design->assign('order_id', $last_order['id']);
                $this->design->assign('cross_orders', $cross_orders);
                $this->design->assign('asp_code_already_sent', $_SESSION['asp_code_already_sent'] ?? false);

                $this->design->assign('ORGANIZATION_FINLAB', $this->organizations::FINLAB_ID);
                $this->design->assign('ORGANIZATION_VIPZAIM', $this->organizations::VIPZAIM_ID);
                $this->design->assign('ORGANIZATION_AKVARIUS', $this->organizations::AKVARIUS_ID);
                $this->design->assign('ORGANIZATION_DEFAULT', $this->config->default_organization_id);

                $this->design->assign('restricted_mode', (int)$restricted_mode);

                $restricted_mode_logout_hint = $restricted_mode
                    && ($user->balance->zaim_number === 'Нет открытых договоров'
                        || $_SESSION['restricted_mode_logout_hint'] == 1);

                $this->design->assign('restricted_mode_logout_hint', (int)$restricted_mode_logout_hint);

                $cards = array_filter( (array) $cards, function( $card ) use ($organization_id){
                    return empty( $card->deleted );
                } );
                $this->design->assign('cards', $cards);
                $this->design->assign('has_default_card', $this->is_default_card_set($cards));
                $this->design->assign('complaint_topics', $this->tickets->getTopics());

                $this->design->assign('has_vk', !empty($this->vk_api->get($user_id)));

                // добавим кастомную метрику
                $this->custom_metric->addMetricAction($this->custom_metric::GOAL_USER_LOGIN_LK, 1);

                // определим нужно ли показывать ссылку на займы для ИП и ООО
                $active_company__orders = $this->company_orders->getItems(['user_id' => $user_id, 'status' => [
                    $this->company_orders::STATUS_NEW,
                    $this->company_orders::STATUS_REJECT,
                    $this->company_orders::STATUS_APPROVED,
                ]]);
                $check_day_limit_co = $this->company_orders->checkShowHref();

                $active_loans = array_filter($response_balances, function($item) {
                    return !empty($item['НомерЗайма']) && $item['НомерЗайма'] != 'Нет открытых договоров';
                });

                $this->design->assign('has_active_loans', !empty($active_loans));
                $this->design->assign('existTg', empty($active_loans) || $this->users->checkTgNickname($this->user->phone_mobile ?? ''));
                $this->design->assign('show_company_form', $check_day_limit_co && empty($active_company__orders) && !empty($active_loans));

                $this->design->assign('payment_methods_btn', $this->settings->payment_methods_btn);

                // признак блокировки рекламных смс
                $blocked_adv_sms = $this->blocked_adv_sms->getItemByUserId($user_id);
                $this->design->assign('blocked_adv_sms', $blocked_adv_sms);

                // Карты МКК "Аквариус", на которые ранее был выдан заем
                $cardsWithIssuedLoans = $this->getCardsWithIssuedLoans($orders, $cards);
                $this->design->assign('cards_with_issued_loans', $cardsWithIssuedLoans);

                $orderForChoosingCard = $this->getOrderForChoosingCard($last_order, $cross_orders, $divide_order);
                /** На основании этого заказа мы определяем какая карта выбрана card_id, если это карта Boostra выводим предупреждение */
                if(isset($orderForChoosingCard['card_id'])) {
                    $cardForOrder = $this->best2pay->get_card($orderForChoosingCard['card_id']);
                    if(isset($cardForOrder->organization_id) && ((int)$cardForOrder->organization_id === Organizations::BOOSTRA_ID)) {
                        $this->design->assign('is_need_choose_card', true);
                    }
                }

                $this->design->assign('order_for_choosing_card', $orderForChoosingCard);

                // проверим пользователя на наличие условий и выключим допы
                $notOverdueLoan = \api\helpers\UserHelper::hasNotOverdueLoan($this, $this->user);

                if (!empty($last_order)) {
                    $this->design->assign('last_order_data', $this->order_data->readAll($last_order['id']));
                }

                $centrifugo_jwt_token = \api\helpers\JWTHelper::generateToken($this->config->CENTRIFUGO['hmac_secret_key'], (int)$this->user->id);
                $this->design->assign('centrifugo_jwt_token', $centrifugo_jwt_token);

                // проверим закрытие займа и задачу для автозаявки
                // Если таск в кроне не обработан и он есть, то отображаем заглушку в ЛК
                if ($last_order['1c_status'] === Orders::ORDER_1C_STATUS_CLOSED) {
                    if ($orderAutoApproveTask = $this->orders_auto_approve->getActiveTask((int)$this->user->id))
                    {
                        $dateCreatedTask = new DateTime($orderAutoApproveTask->date_added);
                        $dateEndViewCounter = new DateTime('-15 minutes');
                        $seconds = $dateCreatedTask->getTimestamp() - $dateEndViewCounter->getTimestamp();

                        // отобразим время сколько осталось от 15 минут
//                        if ($seconds > 0) {
//                            $this->design->assign('auto_approve_seconds_task', $seconds);
//                        }
                    }
                }

                if (!empty($_COOKIE['utm_source'])) {
                    $this->userUtm->create($this->user->id);
                }

                $show_payment_options_modal = isset($_GET['payment-options']) && !empty($this->user->id);
                $this->design->assign('show_payment_options_modal', $show_payment_options_modal);

                $this->design->assign('autoconfirm_enabled', $this->autoconfirm->is_enabled($this->user));

                $this->design->assign('has_cancelled_payment_rs', $this->payment->getHasCancelledPaymentRs($user_id));

                $this->design->assign('payment_rs_data', $this->payment->getPaymentRsData($user_id));

                $this->design->assign('faq_highlight_enabled', $this->settings->faq_highlight_enabled);
                $this->design->assign('faq_highlight_delay', $this->settings->faq_highlight_delay);

                return $this->design->fetch('user.tpl');
            }
        }

        return false;
    }

    /**
     * @param $user_id
     * @return bool
     */
    public function checkOrderAndProlongation($user_id): bool
    {
        if (!empty($user_id)) {
            $first_contract = $this->orders->isFirstOrder($user_id);
            $prolongation_zero = $this->users->isProlongationZero($user_id);

            return ($first_contract && $prolongation_zero);
        }
        return false;
    }

    private function get_user_cards($user)
    {
        if ($user->uid == "Error") {
            return [];
        }

        $cards = [];
        $b2p_enabled = $this->settings->b2p_enabled || $this->user->use_b2p;
        if ($b2p_enabled) {
            return array_map(function ($card) {
                $this->set_is_default_card($card);

                $card->autodebiting = false;
                $card->rebill_id = false;

                return $card;
            }, $this->best2pay->get_cards([
                'user_id' => $user->id,
                'deleted' => 0,
                'deleted_by_client' => 0,
            ]));
//            alter table b2p_cards
//    add deleted_by_client tinyint(1) default 0 null;
        }

        $soap_cards = $this->notify->soap_get_card_list($user->uid);

        if ($soap_cards) {
            foreach ($soap_cards as $card) {
                if ($card->Status == 'A') {
                    $new_card = new stdClass();
                    $new_card->id = $card->CardId;
                    $new_card->pan = $card->Pan;
                    $new_card->autodebiting = $card->AutoDebiting ?? 0; // @todo этого признака нет в АПИ Тинька https://acdn.tinkoff.ru/static/documents/merchant_api_protocoI_e2c_v2.pdf стр. 30
                    $new_card->rebill_id = $card->RebillId;

                    $this->set_is_default_card($new_card);

                    $cards[] = $new_card;
                }
            }
        }
        /*
                // Получение ссылки для привязки карты через 1с
                  $add_card = $this->notify->soap_add_card($user->uid);
                  $user->add_card = $add_card->PaymentURL;
        */

        // получаем ссылку на привязку карты через тиньков
        $add_card = $this->tinkoff->add_card($user->uid);
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($add_card);echo '</pre><hr />';
        // костыль для неправильно обьединенных терминалов
        if (isset($add_card['error']) && $add_card['error'] == 'Найдено больше одного CustomerKey') {
            $this->tinkoff->remove_customer($user->uid);
            $add_card = $this->tinkoff->add_card($user->uid);
        }
        $user->add_card = $add_card['PaymentURL'];

        return $cards;
    }

    private function set_is_default_card($card)
    {
        $card->default = isset($_COOKIE['card_pay_id']) && $_COOKIE['card_pay_id'] == $card->id;
    }

    private function is_default_card_set($cards)
    {
        foreach ($cards as $card) {
            if ($card->default) {
                return true;
            }
        }

        return false;
    }

    private function get_new_contracts($order_id)
    {
        $order = $this->orders->get_crm_order($order_id);
//        $manager = $this->managers->get_crm_manager($order->manager_id);

        //gray button
        $gray = $this->orders->check_order_1c($order->id_1c);

        // хз что это за блок ниже, 3 раза однои тоже вызывалось
        return [
            'gray_contract' => $gray->return->Файл,
            'green_contract' => $gray->return->Файл,
            'new_contract' => $gray->return->Файл,
        ];

        //green button
        if ($order->percent == 0) {
            $discount_rate = 0;
        } else {
            $discount_rate = $order->percent - ($order->percent * $this->settings->additional_services_settings['amount_of_discount'] / 100);
        }
        if ($discount_rate < 0) {
            $discount_rate = 0;
        }
        $green = $this->orders->check_order_1c($order->id_1c);

        //возвращаем как было
        $new = $this->orders->check_order_1c($order->id_1c);

        return [
            'gray_contract' => $gray->return->Файл,
            'green_contract' => $green->return->Файл,
            'new_contract' => $new->return->Файл,
        ];
    }

    /**
     * Parse data from balances and returns loan buyers
     *
     * @param $response_balances array response from soap::get_user_balances_array_1c() method
     *
     * @return array
     */
    private function parseLoanBuyers(array $response_balances): array
    {
        $loan_buyers = [];

        foreach ($response_balances as $response_balance) {

            if (empty($response_balance['Портфель'])) {
                continue;
            }

            foreach ((array)$response_balance['Портфель'] as $buyer_data) {
                /**
                 * Convert input data: 'Портфель' => 'МБА 24.07.2023',
                 * To output:
                 * [
                 *      'loan_number'     => Б23-1239783, // Берется из данных на уровень выше
                 *      'loan_date'       => 06.05.2023,  // Берется из данных на уровень выше
                 *      'loan_buy_date'   => 24.07.2023,
                 *      'loan_buyer_name' => ООО "М.Б.А. ФИНАНСЫ",
                 * ]
                 */
                preg_match('@^(.+?)\s(\d{2}\.\d{2}\.\d{2,4})$@', $buyer_data, $matches);

                $buyer_organization = $matches[1]          ?: null;
                $buy_date           = isset( $matches[2] ) ? date( 'd.m.Y', strtotime( $matches[2] ) ) : null;

                switch( $buyer_organization ){
                    // You can add another organization here
                    case 'МБА':
                        $loan_buyer_organization = 'ООО "М.Б.А. ФИНАНСЫ"';
                        break;
                    case 'Boostra':
                        $loan_buyer_organization = 'ООО "БИКЭШ"';
                        break;
                    case 'СКА':
                        $loan_buyer_organization = 'ООО "Сибирское коллекторское агентство" 88006008384';
                        break;
                    default:
                        $loan_buyer_organization = $matches[1];
                }

                if( ! isset( $response_balance['НомерЗайма'], $response_balance['ДатаЗайма'], $buyer_organization, $buy_date ) ){
                    continue;
                }

                $loan_buyers[] = [
                    'loan_number'     => $response_balance['НомерЗайма'],
                    'loan_date'       => date( 'd.m.Y', strtotime( $response_balance['ДатаЗайма'] ) ),
                    'loan_buy_date'   => $buy_date,
                    'loan_buyer_name' => $loan_buyer_organization,
                ];
            }
        }

        return $loan_buyers;
    }

    private function check_need_add_fields()
    {
        /** Дозаполнение полей */
        $need_add_fields = array();
        if (empty($this->user->lastname))
            $need_add_fields[] = 'lastname';
        if (empty($this->user->firstname))
            $need_add_fields[] = 'firstname';
        //if (empty($this->user->patronymic))
        //    $need_add_fields[] = 'patronymic';

        if (empty($this->user->gender))
            $need_add_fields[] = 'gender';
        if (empty($this->user->birth))
            $need_add_fields[] = 'birth';
        if (empty($this->user->birth_place))
            $need_add_fields[] = 'birth_place';
        if (empty($this->user->marital_status))
            $need_add_fields[] = 'marital_status';

        if (empty($this->user->passport_serial))
            $need_add_fields[] = 'passport_serial';
        if (empty($this->user->passport_date))
            $need_add_fields[] = 'passport_date';
        if (empty($this->user->subdivision_code))
            $need_add_fields[] = 'subdivision_code';
        if (empty($this->user->passport_issued))
            $need_add_fields[] = 'passport_issued';

        if (empty($this->user->Regindex) || empty($this->user->Regregion))
            $need_add_fields[] = 'regaddress';

        if (empty($this->user->Faktindex) || empty($this->user->Faktregion))
            $need_add_fields[] = 'faktaddress';

//        $contactpersons = $this->contactpersons->get_contactpersons(array('user_id' => $this->user->id));
//        if (empty($contactpersons))
//            $need_add_fields[] = 'contactpersons';

//        if (empty($this->user->work_scope))
//            $need_add_fields[] = 'work_scope';
        if (empty($this->user->income_base))
            $need_add_fields[] = 'income_base';

        if ($this->user->work_scope != 'Пенсионер') {
//            if (empty($this->user->Workregion) || empty($this->user->Workhousing))
//                $need_add_fields[] = 'workaddress';

            if (empty($this->user->work_scope) || empty($this->user->profession) || empty($this->user->workplace))
                $need_add_fields[] = 'workdata';
        }

        return $need_add_fields;
    }

    /**
     * Получить карты МКК "Аквариус", на которые ранее был выдан заем
     *
     * @param array $orders
     * @param array $cards
     * @return array
     */
    private function getCardsWithIssuedLoans(array $orders, array $cards): array
    {
        // Смена карт возможна только на карты МКК "Аквариус"
        $akvariusCards = [];
        foreach ($cards as $card) {
            if ((int)$card->organization_id === $this->organizations::AKVARIUS_ID) {
                $akvariusCards[$card->id] = $card;
            }
        }

        if (empty($akvariusCards)) {
            return [];
        }

        $cardsWithIssuedLoans = [];

        foreach ($orders as $order) {
            if ((int)$order->status === $this->orders::STATUS_CONFIRMED && !empty($akvariusCards[$order->card_id])) {
                $cardsWithIssuedLoans[$order->card_id] = $akvariusCards[$order->card_id];
            }
        }

        return $cardsWithIssuedLoans;
    }

    /**
     * Получить заявку для "Выбрать карту"
     *
     * @param $last_order
     * @param $cross_orders
     * @param $divide_order
     * @return array
     */
    private function getOrderForChoosingCard($last_order, $cross_orders, $divide_order): array
    {
        if (!empty($cross_orders) && is_array($cross_orders)) {
            foreach ($cross_orders as $cross_order) {
                if (empty($cross_order['noactive'])) {
                    return (array)$cross_order;
                }
            }
        }

        if (!empty($divide_order) && is_object($divide_order)) {
            $mainOrder = $this->orders->get_order($divide_order->data->main_order_id);

            if (!empty($mainOrder)) {
                return (array)$mainOrder;
            }
        }

        return (array)$last_order;
    }

    function checkPayCredit($data) {
        if ($data->got_gift == 0) {
            if (empty($data->sms_time) || (time() - strtotime($data->sms_time)) <= 86400) {
                return true;
            }
        }
        return false;
    }

    /**
     * Получитьпоследний ответ из кабутек
     */
    private function getLastVerification(int $userId): object
    {
        $cyberityScorings = $this->scorings->get_scorings([
            'user_id' => $userId,
            'type' => $this->scorings::TYPE_CYBERITY,
            'status' => [
                $this->scorings::STATUS_COMPLETED
            ],
            'success' => 0
        ]);
        $lastCyberityScoring = array_shift($cyberityScorings);
        $body = $this->scorings->get_scoring_body((int)$lastCyberityScoring->id);

        return (object)json_decode((string)$body, true);
    }

    /**
     * Метод проверки верификации кабутек
     */
    private function isNeedPhotoVerification(int $userId): bool
    {
        $body = $this->getLastVerification($userId);
        if(!empty($body->reviewResult->reviewAnswer)){
            return ($body->reviewResult->reviewAnswer ==='RED');
        }

        return false;
    }

    /** Обрабатываем utm, пришедшее с колекшна */
    public function processCollectionUtm(\StdClass $user, \StdClass $userBalance)
    {
        $utm = $_COOKIE['source_for_pay'];
        if (!$utm) {
            return;
        }

        $collectionPromo = strpos($utm, 'collection_promo') !== false && $userBalance->discount_amount > 0;
        if (!$collectionPromo) {
            $this->design->assign('collectionPromo', false);

            return;
        }

        $this->design->assign('collectionPromo', true);

        /** Дополнительные скрипты js для модалки конкретной акции */
        $additionalScripts = [];
        /** Заголовок модалки */
        $collectionPromoTitle = null;
        /** Сообщение в модалке */
        $collectionPromoMessage = null;
        /** Название доки с правилами акции */
        $collectionPromoDoc = null;

        $userBalance = $user->balance;
        /** Старая сумма до акции */
        $collectionPromoOldAmount =
            $userBalance->ostatok_od
            + $userBalance->ostatok_percents
            + $userBalance->ostatok_peni
            + $userBalance->penalty;

        /** Сумма со скидкой по акции */
        $collectionPromoNewAmount = $collectionPromoOldAmount - $user->balance->discount_amount;

        /** Акция к 8 марта */
        if (strpos($utm, '08_march') !== false) {
            $collectionPromoTitle = 'Лёгкость этой весны! ';
            $collectionPromoSubTitle = 'Погасите задолженность — порадуйте себя!';
            $collectionPromoMessage = '8 марта — для вас, а не для тревог!';
            $collectionPromoDoc = '8-march';
            $additionalScripts[] = 'showRose';
        }

        /** Акция к НГ */
        if (strpos($utm, 'happy_new_year') !== false) {
            $collectionPromoTitle = 'В Новый год без долгов';
            $collectionPromoSubTitle = '';
            $collectionPromoMessage = 'Начните Новый год без долгов!';
            $collectionPromoDoc = 'happy-new-year';
            $additionalScripts[] = 'snowFlakes';
        }

        $this->design->assign('additional_scripts', $additionalScripts);
        $this->design->assign('collectionPromoTitle', $collectionPromoTitle);
        $this->design->assign('collectionPromoSubTitle', $collectionPromoSubTitle);
        $this->design->assign('collectionPromoMessage', $collectionPromoMessage);
        $this->design->assign('collectionPromoDoc', $collectionPromoDoc);

        $this->design->assign('collectionPromoOldAmount', $collectionPromoOldAmount);
        $this->design->assign('collectionPromoNewAmount', $collectionPromoNewAmount);

        return [
            '$additionalScripts' => $additionalScripts,
            '$collectionPromoTitle' => $collectionPromoTitle,
            '$collectionPromoMessage' => $collectionPromoMessage,
            '$collectionPromoDoc' => $collectionPromoDoc,
            '$collectionPromoOldAmount' => $collectionPromoOldAmount,
            '$collectionPromoNewAmount' => $collectionPromoNewAmount,
        ];
    }

    /**
     * Проверим пользователя на наличие признака к переходу на страницу подписания документов для автовыдачи НК
     * @return void
     */
    private function checkAutoConfirmNewUser()
    {
        if (!$this->user_data->read($this->user->id, $this->user_data::AUTOCONFIRM_FLOW)) {
            return;
        }

        $this->request->redirect($this->config->root_url . '/autoconfirm-asp');
    }
}
