<?php
require_once 'View.php';

class PaymentView extends View
{
    use \api\traits\JWTAuthTrait;

    public function fetch()
    {
        if (empty($this->user) && ($this->request->method('post') && !empty($this->request->post('prolongation_link')))){
            $this->user = $this->users->get_user((int)$this->request->post('user_id'));
        }

        $this->jwtAuthValidate();
        
        //$b2p_enabled = $this->settings->b2p_enabled || $this->user->use_b2p;
        // для явсех оплата через б2п
        $b2p_enabled = 1;
        
        if ($this->request->method('post'))
        {
            // общие данные
            $order_id = $this->request->post('order_id', 'integer');
            $this->design->assign('order_id', $order_id);

            if (!empty($b2p_enabled))
            {
                $this->b2p_payment();
            }
            else
            {
                $this->tinkoff_payment();
            }
        }

        if (!empty($b2p_enabled))
            return $this->design->fetch('b2p_payment.tpl');
        else
            return $this->design->fetch('payment.tpl');
    }
    
    private function tinkoff_payment()
    {
        $amount = str_replace(',', '.', $this->request->post('amount'));
        $user_balance = $this->users->get_user_balance(intval($this->user->id));
        
        $max_payment = $user_balance->ostatok_od + $user_balance->ostatok_percents + $user_balance->ostatok_peni + $user_balance->penalty;
        
        if ($amount > $max_payment)
        {
            $this->design->assign('error', 'Максимальная сумма к оплате: '.$max_payment.' руб');
            $amount = $max_payment;
        }
        elseif ($max_payment != $amount && ($max_payment - $amount) < 1)
        {
            $this->design->assign('error', 'нельзя оставлять долг меньше 1 руб');                
            $amount = $max_payment;
        }
        
        $this->design->assign('amount', $amount);
        
        
        $card_list = $this->notify->soap_get_card_list($this->user->uid);

        $cards = array();
        if($card_list)
        {
			foreach($card_list as $card)
            {
    			if($card->Status =='A' && !empty($card->RebillId)) 
                {
                    $card = (array)$card;
                    $card['ExpDate'] = substr($card['ExpDate'], 0, 2).'/'.substr($card['ExpDate'], 2, 2);
                    $cards[] = $card;
                }
            }
		}
        $this->design->assign('cards', $cards);

        // пролонгация
        $insure = $this->request->post('insure', 'integer');
        $prolongation = $this->request->post('prolongation', 'integer');
        $code_sms = $this->request->post('code', 'string');



        $this->design->assign('insure', $insure + ($amount < $user_balance->penalty ? 0 : $user_balance->penalty));
        $this->design->assign('prolongation', $prolongation);
        $this->design->assign('code_sms', $code_sms);
        
        
        $insurer = $this->orders->get_insure_ip();
        $this->design->assign('insurer', $insurer);

        $payment_day = date('Y-m-d 00:00:00', strtotime($user_balance->payment_date));
        if (strtotime($payment_day) < strtotime(date('Y-m-d 00:00:00')))
        {
            $this->design->assign('have_exitpool', 1);
        }
        
        $exitpool_variants = $this->payment_exitpools->get_variants();
        $this->design->assign('exitpool_variants', $exitpool_variants);

        setcookie('paypage', 1, time()+86400, '/');
    }

    /**
     * @return void
     */
    private function b2p_payment(): void
    {
        $prolongation = $this->request->post('prolongation', 'integer');
        $prolongation_data = $_SESSION['prolongation_data'] ?? [];

        if (empty($prolongation)) {
            unset($_SESSION['prolongation_data']);
        }

        $amount = !empty($prolongation) ? $prolongation_data['amount'] : str_replace(',', '.', $this->request->post('amount'));
        $gracePayment = $this->request->post('grace_payment', 'boolean');
        $calc_percents = !empty($prolongation) ? $prolongation_data['calc_percents'] : $this->request->post('calc_percents', 'integer');
        $number = !empty($prolongation) ? $prolongation_data['number'] : $this->request->post('number');
        $code_sms = $this->request->post('code', 'string');
        $chdp = $this->request->post('chdp', 'integer');
        $pdp = $this->request->post('pdp', 'integer');
        $refinance = $this->request->post('refinance', 'integer');
        $from = $this->request->post('from', 'string');
        $order_id = $this->request->post('order_id', 'integer');
        $order = $this->orders->get_crm_order($order_id);
        $amthash = $this->request->post('amthash', 'string');
        
        $payment_logs = [];
        $payment_logs['total_debt'] = $this->request->post('total_debt', 'integer');
        $payment_logs['ostatok_od'] = $this->request->post('ostatok_od', 'integer');
        $payment_logs['ostatok_percents'] = $this->request->post('ostatok_percents', 'integer');
        $payment_logs['ostatok_peni'] = $this->request->post('ostatok_peni', 'integer');
        $payment_logs['penalty'] = $this->request->post('penalty', 'integer');
        $payment_logs['button_name'] = $this->request->post('button_name', 'string');
        $payment_logs['tv_medical_amount'] = $this->request->post('tv_medical_amount', 'integer');
        $payment_logs['star_oracle_amount'] = $this->request->post('star_oracle_amount', 'integer');
        $payment_logs['multipolis_amount'] = $this->request->post('multipolis_amount', 'integer');
        $payment_logs['half_additional_service_repayment'] = $this->request->post('half_additional_service_repayment', 'integer');
        $payment_logs['additional_service_repayment'] = $this->request->post('additional_service_repayment', 'integer');
        $payment_logs['half_additional_service_so_repayment'] = $this->request->post('half_additional_service_so_repayment', 'integer');
        $payment_logs['additional_service_so_repayment'] = $this->request->post('additional_service_so_repayment', 'integer');
        
        if (!empty($amthash)) {
            $amt = base64_decode($amthash);
            if ($amt != $amount) {
                $amount = $amt;
            }
        }
        
        $multipolis_amount = ((!empty($prolongation) && isset($prolongation_data['multipolis_amount'])) ? $prolongation_data['multipolis_amount'] : $this->request->post('multipolis_amount', 'integer')) * (int)$order->additional_service_multipolis;
        $tv_medical_id = (!empty($prolongation) && isset($prolongation_data['tv_medical_id'])) ? $prolongation_data['tv_medical_id'] : $this->request->post('tv_medical_id', 'integer');
        $tv_medical_amount = (!empty($prolongation) && isset($prolongation_data['tv_medical_amount'])) ? $prolongation_data['tv_medical_amount'] : $this->request->post('tv_medical_amount', 'integer');
        $star_oracle_amount = (!empty($prolongation) && isset($prolongation_data['star_oracle_amount'])) ? $prolongation_data['star_oracle_amount'] : $this->request->post('star_oracle_amount', 'integer');
        $star_oracle_id = (!empty($prolongation) && isset($prolongation_data['star_oracle_id'])) ? $prolongation_data['star_oracle_id'] : $this->request->post('star_oracle_id', 'integer');


        $i = 1;

        start:
        $response_balances = $this->soap->get_user_balances_array_1c($this->user->uid);

        if ($i < 2 && isset($response_balances['errors']) && $response_balances['errors']) {
            sleep(1); // пауза для повторного обращения к 1c, если произошла ошибка
            ++$i;
            goto start;
        }
        $full_payment_amount = null;

        foreach ($response_balances as $response_balance) {
            if ($response_balance['НомерЗайма'] == $number) {

                $user_balance = $this->users->make_up_user_balance($this->user->id, (object)$response_balance);
                $full_payment_amount = $response_balance['ОстатокОД'] + $response_balance['ОстатокПроцентов'] + $response_balance['ШтрафнойКД'];
            }
        }

        if (empty($user_balance)) {
            setcookie("error", 'Пожалуйста, попробуйте еще раз.', time() + 60);
            header('Location: ' . $this->config->root_url . '/user');
            exit;
        }
        setcookie("error", "", time() - 3600);

        $restricted_mode = $_SESSION['restricted_mode'] == 1;
        if ($restricted_mode && $this->best2pay->checkDebtAndPromo($user_balance, $user_balance->discount_amount, $amount, $prolongation)) {
            $this->design->assign('discount', $user_balance->discount_amount);
        } else {
            $this->design->assign('discount', 0);
        }

        $user_balance->calc_percents = $this->users->calc_percents($user_balance);

        $organization_id = $this->users->get_organization_id($response_balances, $user_balance);

        if ($this->settings->sbp_enabled && $organization_id == $this->organizations::AKVARIUS_ID) {
            $sbp_enabled = true;
        } else {
            $sbp_enabled = false;
        }
        $this->design->assign('sbp_enabled', $sbp_enabled);
        $this->design->assign('organization_id', $organization_id);

        if ($user_balance->loan_type == 'IL') {
            $user_balance->details = $this->soap->get_il_details($user_balance->zaim_number);
            $max_payment = round($user_balance->details['ОбщийДолг'] + $tv_medical_amount + $multipolis_amount + $star_oracle_amount);
        } else {
            $max_payment = round($user_balance->ostatok_od + $tv_medical_amount + $multipolis_amount + $star_oracle_amount + $user_balance->ostatok_percents + $user_balance->ostatok_peni + $user_balance->penalty, 2);
        }
        if (!empty($gracePayment)) {
            $amount = str_replace(',', '',number_format($user_balance->sum_od_with_grace + $user_balance->sum_percent_with_grace,2));
        }
        if (!empty($calc_percents)) {
            $max_payment += $user_balance->calc_percents;
        }

        if ($amount > $max_payment) {
            $this->design->assign('error', 'Максимальная сумма к оплате: ' . $max_payment . ' руб');
            $amount = $max_payment;
        } elseif ($max_payment != $amount && ($max_payment - $amount) < 1) {
            $this->design->assign('error', 'нельзя оставлять долг меньше 1 руб');
            $amount = $max_payment;
        }


        $action_type = $this->star_oracle::ACTION_TYPE_PROLONGATION;

        if (empty($prolongation)) {
            $total_ostatok = round($user_balance->ostatok_od + $user_balance->ostatok_percents + $user_balance->ostatok_peni, 2);
            $diff = round($amount - $total_ostatok, 2);
            if ($diff >= $user_balance->penalty) {
                $params['insure'] = $user_balance->penalty;
            }

            if ($max_payment > $amount) {
                $action_type = $this->star_oracle::ACTION_TYPE_PARTIAL_PAYMENT;
            } else {
                $action_type = $this->star_oracle::ACTION_TYPE_FULL_PAYMENT;
            }
        }

        $card_list = $this->best2pay->get_cards([
            'user_id' => $this->user->id,
            'organization_id' => $organization_id,
            'deleted' => 0,
            'deleted_by_client' => 0
        ]);

        $cards = [];
        if ($card_list) {
            foreach ($card_list as $card) {
                $cards[] = $card;
            }
        }
//        $basicCard = $this->users->getBasicCard($this->user->id);
//        $this->design->assign('basicCard', $basicCard);

        if ($full_payment_amount + $tv_medical_amount + $star_oracle_amount == $amount) {
            $_SESSION['full_payment_amount'] = $amount;
        }else{
            $_SESSION['full_payment_amount'] = null;
        }

        if ($order->additional_service_multipolis == 1) {
            if ($user_balance->loan_type == 'IL') {
                $multipolis = !empty($prolongation) ? $prolongation_data['multipolis'] : $this->request->post('multipolis', 'integer');
                $use_multipolis_amount = $multipolis_amount;
            } else {
                $multipolis = !empty($prolongation) ? $prolongation_data['multipolis'] : null;
                $use_multipolis_amount = !empty($prolongation) ? $multipolis_amount : null;
            }
        } else {
            $multipolis = null;
            $use_multipolis_amount = null;
        }

        // оплата на частичке с допами
        $hidden_amount = $this->request->post('hidden_amount');
        if ($user_balance->loan_type == 'IL' && $hidden_amount) {
            $amount = $hidden_amount;
        }

        $params = [
            'gracePayment' => $gracePayment,
            'calc_percents' => !empty($calc_percents),
            'amount' => $amount,
            'organization_id' => $organization_id,
            'cards' => $cards,
            'prolongation' => $prolongation,
            'multipolis' => $multipolis,
            'multipolis_amount' => $use_multipolis_amount,
            'tv_medical' => ($order->additional_service_tv_med == 1 && !empty($prolongation)) ? $prolongation_data['tv_medical'] : (int)($this->request->post('tv_medical', 'integer') && $tv_medical_amount > 0),           
            'tv_medical_amount' => $tv_medical_amount,
            'tv_medical_id' => $tv_medical_id,
            'star_oracle' => (int) ($this->request->post('star_oracle', 'integer') && $star_oracle_amount > 0),
            'star_oracle_id' => $star_oracle_id,
            'star_oracle_amount' => $star_oracle_amount,
            'code_sms' => $code_sms,
            'number' => $number,
            'chdp' => $chdp,
            'pdp' => $pdp,
            'from' => $from,
            'refinance' => $refinance,
            'insurer' => 'ST',
            'have_exitpool' => strtotime($user_balance->payment_date) < strtotime(date('Y-m-d 00:00:00')),
            'exitpool_variants' => $this->payment_exitpools->get_variants(),
            'insure' => $params['insure'] ?? null,
            'action_type' => $action_type
        ];

        $this->logging(__METHOD__, 'payment view assign', (array)$params, ['user_balance' => array($user_balance), 'prolongation_data' => $prolongation_data, 'payment_logs' => $payment_logs, 'max_payment' => $max_payment], 'b2p_payment.txt');

        $this->design->assignBulk($params);

        setcookie('paypage', 1, time() + 86400, '/');

    }

}
