<?php

require_once 'View.php';

class SchedulePaymentsView extends View
{
    public function fetch()
    {
        if (!($number = $this->request->get('number'))) {
            return false;
        }
        
        if (!($contract = $this->contracts->get_contract_by_params(['number'=>$number]))) {
            return false;
        }
        
        if ($contract->user_id != $this->user->id) {
            return false;
        }
        
        $schedule_payments = $this->soap->get_schedule_payments($number);
        $schedule_payments = (array)end($schedule_payments);
        $schedule_payments['Платежи'] = array_map(function($var){
            return (array)$var;
        }, $schedule_payments['Платежи']);
        
        $this->design->assign('schedule_payments', $schedule_payments);
        $this->design->assign('contract', $contract);
        
        return $this->design->fetch('installment/schedule_payments.tpl');
    }
}