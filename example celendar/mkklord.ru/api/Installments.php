<?php

require_once('Simpla.php');

class Installments extends Simpla
{
    /**
     * Интервал в днях после выдачи займа когда становятся доступны заявление на ПДП|ЧДП
     */
    const ACCEPT_INTERVAL = 14;    
    
    /**
     * Installments::get_installments_enabled()
     * Возвращает активны ли инстоллменты
     * 
     * @return bool
     */
    public function get_installments_enabled()
    {
        $installment_test_users = array_map('trim', explode(',', $this->settings->installment_test_users));
        return $this->settings->installments_enabled || $this->is_developer || in_array($_SESSION['user_id'], $installment_test_users);
    }
    
    /**
     * Installments::get_loan_type()
     * Возвращает тип займа в зависимости от срока займа
     * 
     * @param int $period
     * @return string
     */
    public function get_loan_type($period)
    {
        $installments_enabled = $this->get_installments_enabled();
        if ($installments_enabled && $period > 30) {
            return 'IL';
        } else {
            return 'PDL';
        }
    }
    
    /**
     * Installments::check_enabled()
     * Проверяет доступен ли клиенту калькулятор для подачи заявки с инстоллмент займами
     * 
     * @param int $user_id
     * @return int
     */
    public function check_enabled($user)
    {
        // калькулятор отключен, ИЛ только через скористу
        return 0;
        
        $installments_enabled = $this->get_installments_enabled();
        if (empty($installments_enabled))
            return 0;
        
        if (!empty($user->loan_history)) {
            $loan_close_count = 0;
            $loan_total_days = 0;
            foreach ($user->loan_history as $history_item) {
                if (!empty($history_item->close_date)) {
                    $loan_close_count++;
                    
                    $origin = date_create(date('Y-m-d', strtotime($history_item->date)));
                    $target = date_create(date('Y-m-d', strtotime($history_item->close_date)));
                    $interval = date_diff($origin, $target);
                    $loan_total_days += $interval->format('%a');
                }
            }
            $loan_avg_days = intval($loan_total_days / $loan_close_count);

            $max_overdue = $this->soap->MaxOverdueByClient($user->uid);
            $segments = $this->get_segments();
            foreach ($segments as $seg) {
                if ($loan_close_count >= $seg->min_close_count 
                    && $loan_avg_days >= $seg->history_avg_days
                    && $max_overdue <= $seg->history_max_expired 
                ) {
                    return 1;
                }
            }            
        }
        
        return 0;    	
    }
    
    
    /**
     * Installments::check_accept()
     * Проверяет нужно ли клиенту подписывать заявление на ПДП|ЧДП
     * 
     * @param string $loan_date
     * @return int
     */
    public function check_accept($issuance_date)
    {
        $loan_date = date_create(date('Y-m-d', strtotime($issuance_date)));
        $today_date = date_create(date('Y-m-d'));
        $interval = date_diff($loan_date, $today_date);
        
        return intval($interval->format('%a') > self::ACCEPT_INTERVAL);        
    }

	/**
	 * Installments::get_segment()
	 * Получает сегмент для инстоллмент займов по id
     * 
	 * @param integer $id
	 * @return object
	 */
	public function get_segment($id)
	{
		$query = $this->db->placehold("
            SELECT * 
            FROM __installment_segments
            WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
        $result = $this->db->result();
	
        return $result;
    }
    
	/**
	 * Installments::get_segments()
	 * Получает список всех сегментов для инстоллмент займов
     * 
	 * @param void
	 * @return array
	 */
	public function get_segments()
	{
        $query = $this->db->placehold("
            SELECT * 
            FROM __installment_segments
            ORDER BY id ASC
        ");
        $this->db->query($query);
        $results = $this->db->results();
        
        return $results;
	}
}