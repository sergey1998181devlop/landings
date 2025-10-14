<?php

use api\asp\AspHelper;

session_start();
chdir('..');

require_once 'api/Simpla.php';
require_once('api/Helpers.php');

class LoanAjax extends Simpla
{
    private $response = array();
    
    private $sms_delay = 30;
    
    public function run()
    {
    	$action = $this->request->get('action', 'string');
        
        switch($action):
            case 'check_user':
                $this->check_user_action();
            break;
            case 'change_period_and_percent':
                $this->change_period_and_percent();
            break;
            case 'send_code':
                if (empty($this->is_developer)
                    && !$this->recaptcha->check($this->request->post('g-recaptcha-response'))
                    && (isset($_COOKIE['utm_source'])
                        && $_COOKIE['utm_source'] != 'leadgid'
                        && !empty($this->settings->captcha_status)))
                {
                    $this->response['error'] = 'recaptcha_error';
                }
                else                
                {
                    $need_check = intval($this->request->post('need_check'));
                    $lastname = mb_convert_case(trim($this->request->post('lastname')), MB_CASE_TITLE);
                    $firstname = mb_convert_case(trim($this->request->post('firstname')), MB_CASE_TITLE);
                    $patronymic = mb_convert_case(trim($this->request->post('patronymic')), MB_CASE_TITLE);
                    $birth = trim($this->request->post('birth'));
                    $phone = trim($this->request->post('phone'));
                    
                    if (!empty($phone))
                    {                    
                        $this->send_code_action($phone, $need_check, $lastname, $firstname, $patronymic, $birth);
                    }
                    else
                    {
                        
//                        sleep(30);
                        $this->response = array(
                            "error" => "user_exists",
                            "success" => true,
                            "sms_time" => 0
                        );
                                            
                    }                                        
                }
            break;
            
            case 'check_code':
                $phone = $this->request->get('phone');
                $code = $this->request->get('code');
                
                $this->check_code_action($phone, $code);
            break;
            
            case 'first_loan':
                $this->create_first_loan();
            break;

            case 'get_prolongation':
                $this->get_prolongation();
                break;

            case 'prolongation_amount':
                $this->prolongation_amount_action();
                break;
            
        endswitch;
        
        $this->output();
    }
    
    private function create_first_loan()
    {
        if (!empty($_SESSION['user_id']))
        {
            if ($user = $this->users->get_user((int)$_SESSION['user_id']))
            {
                // отправляем файлы
                $returned = $this->soap->soap_send_files($user->id);
                if ($returned == 'sent' || (!empty($returned->return) && $returned->return == 'OK'))
                {
                    $files = $this->users->get_files(array('user_id'=>$user->id, 'status'=>0));
                    foreach ($files as $file)
                    {                                            
                        $this->users->update_file($file->id, array('status' => 1));
    
                        // удаляем оригинальные файлы, оставляем только ресайзы
//                                            if (file_exists($this->config->root_dir.$this->config->original_images_dir.$file->name))
//                                                unlink($this->config->root_dir.$this->config->original_images_dir.$file->name);
                    }
                    $this->users->update_user($user->id, array(
                        'file_uploaded' => 1, 
                        'files_added' => 1, 
                        'files_added_date' => date('Y-m-d H:i:s'),
                        'missing_real_date' => date('Y-m-d H:i:s')
                    ));
                
                    $this->response['success'] = 1;
                }    
                else
                {
                    $this->users->update_user($user->id, [
                        'accept_data_added' => 0,
                        'utm_source' => ''
                    ]);
                    $this->response['error'] = 'files_not_sent';
                }

            }
            else
            {
                $this->response['error'] = 'undefined_user';
            }
        }
        else
        {
            $this->response['error'] = 'empty_user_id';
        }
        
        
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($this->user);echo '</pre><hr />';
        // создаем order в базе сайта
        
        
        
    }
    
    
    private function check_code_action($phone, $code)
    {
    	$phone = $this->users->clear_phone($phone);

        if ($phone == $_SESSION['phone'] && $code == $_SESSION['sms']) {
            $this->response['success'] = 1;
            $asp_type = $this->request->get('asp_type', 'string');
            if ($asp_type == AspHelper::ASP_TYPE_CONFIRM_REMOVE_ACCOUNT) {
                $this->generateDocAfterASPDeleteUser();
                $this->users->blockedUserCabinet((int)$_SESSION['user_id']);
            }
        } else {
            $this->response['error'] = 1;
        }
    }

    private function generateDocAfterASPDeleteUser()
    {
        $asp = $this->request->get('code');
        $user = $this->users->get_user((int)$_SESSION['user_id']);
        $params = Documents::getParamsForContractDeletedUser($user);
        $params['asp'] = $asp;
        $this->documents->create_document(
            [
                'user_id' => $user->id,
                'type' => Documents::CONTRACT_DELETE_USER_CABINET,
                'params' => $params,
            ]
        );
    }
    
    private function send_code_action($phone, $need_check, $lastname, $firstname, $patronymic, $birth)
    {
        if (empty($_SERVER['HTTP_REFERER']))
        {
            $this->response['error'] = 'Обновите страницу и попробуйте еще раз';
            $this->response['user'] = false;
        }        
        else
        {
            $soap = $this->soap->get_uid_by_phone($phone);
            if (!empty($soap->result) && !empty($soap->uid))
            {                    
                $this->response['error'] = 'user_exists';
            }
            elseif ($soap->error == 'ЛК удален')
            {
                $this->response['error'] = 'user_removed';
                $this->response['block'] = '1';
            }
            elseif ($soap->error == 'Множество совпадений')
            {
                $this->soap->send_doubling_phone($phone);
                
                $this->response['error'] = 'user_blocked';
            }
            else
            {
                // проверка по ФИО на блокировку
                if (!empty($need_check))
                {
                    $client_state = $this->soap->get_client_state($lastname, $firstname, $patronymic, $birth);
                }
                
                if (!empty($need_check) && $client_state == 'Delete')
                {
                    $this->response['error'] = 'fio_removed';
                }
                else
                {
                    $sms_validate = $this->sms_validate->getRow(null, $this->users->clear_phone($phone));
    
                    if ((!empty($_SESSION['sms_time']) && ($_SESSION['sms_time'] + $this->sms_delay) > time())
                        || $this->sms_validate->getBadSmsByTime($_SERVER['REMOTE_ADDR'], $this->users->clear_phone($phone)))
                    {
                        $this->response['error'] = 'sms_time';
    
                        if (empty($_SESSION['sms_time'])) {
                            $sms_time = $sms_validate->sms_time ?? time();
                        } else {
                            $sms_time = $_SESSION['sms_time'];
                        }
    
                        $this->response['time_left'] = $sms_time + $this->sms_delay - time();
                    }
                    else
                    {
                        // проверяем по базе пользователя
                        if ($exist_user = $this->users->get_user((string)$phone))
                        {
                            if ($exist_user->enabled)
                                $this->response['error'] = 'user_exists';
                            else
                            {
                                $this->response['error'] = 'user_removed';
                                $this->response['block'] = '2';
                                $this->response['exists'] = $exist_user;
                            }
                        }
                        else
                        {
                            $code = mt_rand(1000, 9999);
                            $_SESSION['sms'] = $code;
                    		$_SESSION['phone'] = $this->users->clear_phone($phone); 
                            
                            if ($this->is_developer || $this->is_admin)
                            {
                                $this->response['code'] = $code;
                            }
                            else
                            {
                                $border_date = date('Y-m-d H:i:s', time() - 86400);
                                $sent_sms = $this->sms->get_sent_sms($phone, $border_date);
                                
                                $daily_count = 0;
                                $hour_count = 0;
                                $last_sent = NULL;
                                foreach ($sent_sms as $sms_item)
                                {
                                    if (strtotime($sms_item->created) > (time() - 3600))
                                        $hour_count++;
                                    $daily_count++;
                                    
                                    if (empty($last_sent) || strtotime($last_sent) > strtotime($sms_item->created))
                                        $last_sent = $sms_item->created;
                                }
                                
                                if ($daily_count > 20)
                                {
                                    $this->response['error'] = 'Вы исчерпали лимит смс-сообщений. Попробуйте повторить через сутки';
                                }
                                elseif ($hour_count > 6)
                                {
                                    $this->response['error'] = 'Вы исчерпали лимит смс-сообщений. Попробуйте повторить через час';
                                }
                                else
                                {
                                    $sms_total = ($sms_validate->total ?? 0) + 1;
                                    $smart_token = $this->request->post('smart-token') ?: '';
                                    $this->response = array_merge($this->response, \api\helpers\Captcha::validateRequest($sms_total, $smart_token));

                                    if (empty($this->response['captcha'])) {
                                        //$msg = iconv('utf8', 'cp1251', 'Ваш код для регистрации на boostra.ru: '.$_SESSION['sms']);
                                        $msg = 'Ваш код для регистрации на boostra.ru: ' . $_SESSION['sms'];
                                        //Код 1234 Научим эить без долгов! www.goo.su/49Oq
                                        $this->response['answer'] = $this->notify->send_sms($phone, $msg, 'Boostra.ru', 1);
                                        $this->sms->add_message(
                                            [
                                                'phone' => $phone,
                                                'message' => 'Ваш код для регистрации на boostra.ru: ' . $_SESSION['sms'],
                                                'send_id' => $this->response['answer'],
                                                'created' => date('Y-m-d H:i:s', time()),
                                            ]
                                        );
                                    }
                                }
                            }
                            
                            $this->response['success'] = 1;
                            $_SESSION['sms_time'] = time();
    
                            $insert_data = [
                                'phone' => $_SESSION['phone'],
                                'ip' => $_SERVER['REMOTE_ADDR'],
                                'sms_time' => $_SESSION['sms_time'],
                                'total' => ($sms_validate->total ?? 0) + 1
                            ];

                            $this->sms_validate->updateFloodSMS($sms_validate, $insert_data);
                        }
                    }
                    
                    $this->response['success'] = true;
                    if (empty($_SESSION['sms_time']))
                        $this->response['sms_time'] = 0;
                    else
                        $this->response['sms_time'] = ($_SESSION['sms_time'] + $this->sms_delay) - time();
                
                }
            }
    
        }
    }
    
    
    private function output()
    {
  		header("Content-type: application/json; charset=UTF-8");
    	header("Cache-Control: must-revalidate");
    	header("Pragma: no-cache");
    	header("Expires: -1");		
    	
        echo json_encode($this->response);

    }

    /**
     * Меняем процент и период заявки
     * @return void
     */
    private function change_period_and_percent()
    {
        $percent = $this->request->post('percent', 'number');        
        $percent = min($percent, $this->orders::BASE_PERCENTS);
        
        if (!empty($_SESSION['user_id'])) {
            $user_id = (int)$_SESSION['user_id'];

            if($order_id = $this->request->post('order_id'))
            {
                $last_order = $this->orders->get_crm_order($order_id);
            } else {
                $last_order = $this->orders->get_last_order($user_id);
            }

            $manager = $this->managers->get_crm_manager($last_order->manager_id);
            $update_data = [];

            // если период передан сохраним текущее значение, при условии если его не сохраняли ранее
            if (!empty($this->request->post('period')) && empty($last_order->selected_period)) {
                $update_data['selected_period'] = $last_order->period;
            }

            // запишем факт нажатия кнопки в заявку
            if (!empty($this->request->post('accept_button_name')) && empty($last_order->is_default_way) && empty($last_order->is_discount_way)) {
                $button_name = $this->request->post('accept_button_name');
                if (in_array($button_name, ['is_default_way', 'is_discount_way'])) {
                    $update_data[$button_name] = 1;
                }
            }

            // если период не передан возьмем его у заявки
            $period = $this->request->post('period', 'number') ?: ($last_order->selected_period ?: $last_order->period);

            //обновляем процент и период займа в 1с
            $response_1c = $this->soap->update_status_1c($last_order->{'1c_id'}, 'Одобрено', $manager->name_1c, $last_order->amount, $percent, '', 0, $period);
            if ($response_1c === 'OK') {
                $update_data = array_merge($update_data, compact('percent', 'period'));
                $this->orders->update_order($last_order->id, $update_data);

                // получим новый документ из 1С
                $resp = $this->orders->check_order_1c($last_order->{'1c_id'});
                if (!empty($resp->return->Файл)) {
                    $this->response['approved_file'] = $this->config->root_url . '/files/contracts/' .$this->documents->save_pdf($resp->return->{'ФайлBase64'}, $resp->return->{'НомерЗаявки'}, 'Preview_Contracts');
                }
            }
        } else {
            $this->response['error'] = true;
        }
    }

    /**
     * Генерирует контент пролонгации
     * @return void
     * @throws Exception
     */
    private function get_prolongation()
    {
        $order_id = $this->request->post('order_id', 'integer');
        $user_id = $this->request->post('user_id', 'integer');
        $number = $this->request->post('number', 'string');
        $tv_medical_tariff_id = $this->request->post('tv_medical_tariff_id', 'integer');

        $order = $this->orders->get_crm_order($order_id);
        $user = $this->users->get_user($user_id);
        $response_balances = $this->soap->get_user_balances_array_1c($user->uid);

        $order_balance = array_filter($response_balances, function ($item) use ($number) {
            return $item['НомерЗайма'] == $number;
        });
        $balance_1c = (object)array_shift($order_balance);
        $user->balance = $this->users->make_up_user_balance($user_id, $balance_1c);
        $user->balance->calc_percents = $this->users->calc_percents($user->balance);
        $user->order = (array)$order;

        $today = strtotime(date('Y-m-d 00:00:00'));
        if (strtotime($user->balance->payment_date) >= $today) {
            $prolongation_insure_percent = 15;
        } elseif (strtotime($user->balance->payment_date) <= ($today + 86400 * 8)) {
            $prolongation_insure_percent = 25;
        } else {
            $prolongation_insure_percent = 25;
        }

        $multipolis_amount = $this->multipolis->getMultipolisAmount($user);
        $tv_medical_tariffs = $this->tv_medical->getAllTariffs();
        $vitaMedTariffs = $this->tv_medical->getAllVitaMedPrices();

        if (!empty($tv_medical_tariff_id)) {
            $array_medical_filtered = array_filter($vitaMedTariffs, function ($item) use ($tv_medical_tariff_id) {
                return $item->id == $tv_medical_tariff_id;
            });
            $active_tv_medical = array_shift($array_medical_filtered);
        } else {
            $active_tv_medical = $vitaMedTariffs[1];
        }

        $_SESSION['prolongation_data'] = [
                'user_id' => $user_id,
                'number' => $number,
                'calc_percents' => $user->balance->calc_percents,
                'amount' => $user->balance->ostatok_percents + $user->balance->ostatok_peni + $user->balance->calc_percents
                    + ($order->additional_service_multipolis ? $multipolis_amount : 0) + ($order->additional_service_tv_med ? $active_tv_medical->price : 0),
            ] + ($order->additional_service_multipolis ? [
                'multipolis' => 1,
                'multipolis_amount' => $multipolis_amount,
            ] : [])
            + ($order->additional_service_tv_med ? [
                'tv_medical' => 1,
                'tv_medical_id' => $active_tv_medical->id,
                'tv_medical_amount' => $active_tv_medical->price,
            ] : []);

        $this->logging(__METHOD__, 'loan data', $prolongation_insure_percent, [
                'multipolis_amount' => $multipolis_amount,
                'active_tv_medical_price' => $active_tv_medical->price,
                'active_tv_medical' => $active_tv_medical,
                'balance' => $user->balance,
                'prolongation_session' => $_SESSION['prolongation_data'] ?? [],
            ]
            , 'b2p_payment.txt');

        $organization_id = $this->users->getOrganizationIdByOrderId($order_id);

        $this->design->assignBulk([
            'session_id' => session_id(),
            'organization' => $this->organizations->get_organization($organization_id),
            'orderAdditional' => $this->orders->get_crm_order($order_id),
            'tv_medical_tariffs' => $tv_medical_tariffs,
            'vita_med_tariffs' => $vitaMedTariffs,
            'tv_medical_amount' => $active_tv_medical->price,
            'tv_medical_id' => $active_tv_medical->id,
            'multipolis_amount' => $multipolis_amount,
            'prolongation_insure_percent' => $prolongation_insure_percent,
            'user' => $user,
            'expired_days' => $user->balance->expired_days,
            'order_id' => $order_id
        ]);

        $this->design->assign('restricted_mode', $_SESSION['restricted_mode'] == 1);

        $html = $this->design->fetch('prolongation.tpl');
        $this->request->html_output($html);
    }

    /**
     * @throws Exception
     */
    private function calculate_prolongation_amount()
    {
        if (!isset($_SESSION['prolongation_data'])) {
            throw new Exception('Prolongation data not found in session.');
        }

        $data = $_SESSION['prolongation_data'];

        $amount = $data['amount'];

        return $amount;
    }

    public function prolongation_amount_action()
    {
        try {
            $amount = $this->calculate_prolongation_amount();
            $this->response = [
                'success' => true,
                'amount' => number_format($amount, 2, '.', '')
            ];
        } catch (Exception $e) {
            $this->response = [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }

    }

}

$loan = new LoanAjax();
$loan->run();
