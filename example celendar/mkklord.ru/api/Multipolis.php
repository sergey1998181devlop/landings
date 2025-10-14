<?php

require_once 'Simpla.php';
require_once 'interface/BaseModel.php';
require_once( dirname(__DIR__) . '/vendor/autoload.php');

use PHPMailer\PHPMailer\PHPMailer;

/**
 * Class Multipolis
 * Класс для работы с мультиполисами
 */
class Multipolis extends Simpla implements BaseModel
{
    /**
     * Статус новой записи о мультиполисе
     */
    const STATUS_NEW = 'NEW';

    /**
     * Статус оплаченного мультиполиса
     */
    const STATUS_SUCCESS = 'SUCCESS';

    /**
     * Статус не оплаченного мультиполиса
     */
    const STATUS_ERROR = 'ERROR';

    public const IL_DOP_RATE = 0.18;

    /**
     * Добавляет запись о мультиполисе
     * @param array $data
     * @return mixed
     */
    public function addItem(array $data)
    {
        $query = $this->db->placehold("INSERT INTO s_multipolis SET ?%", $data);
        $this->db->query($query);
        $id = $this->db->insert_id();

        // сгенерируем номер полиса
        $number = $this->generateNumber($id);

        // добавляем номер полиса
        $this->updateItem($id, compact('number'));

        return $id;
    }

    /**
     * @param int $id
     * @param string $partner_first_letter
     * @return string
     */
    public function generateNumber(int $id, string $partner_first_letter = 'А')
    {
        $multipolis = $this->selectItemById($id);
        $total = $this->selectTotal(['user_id' => $multipolis->user_id]);
        return $partner_first_letter . $total . '-' . $multipolis->id;
    }

    /**
     * Обновляет запись о мультиполисе
     *
     * @param int   $id
     * @param array $data
     *
     * @return mixed
     */
    public function updateItem( int $id, array $data )
    {
        $query = $this->db->placehold( "UPDATE s_multipolis SET ?% WHERE id = ?", $data, $id );
        
        return $this->db->query( $query );
    }
    
    /**
     * Alias for updateItem
     *
     * @param $id
     * @param $multipolis
     *
     * @return mixed
     */
    public function update_multipolis($id, $multipolis)
    {
		$query = $this->db->placehold("
            UPDATE s_multipolis SET ?% WHERE id = ?
        ", (array)$multipolis, (int)$id);
        $this->db->query($query);
        
        return $id;
    }
    
    /**
     * Удаляет запись о мультиполисе
     * @param int $id
     * @return mixed
     */
    public function deleteItem(int $id)
    {
        $query = $this->db->placehold("DELETE FROM s_multipolis WHERE id = ?", $id);
        return $this->db->query($query);
    }

    /**
     * @param int $id
     * @return mixed|void
     */
    public function selectItemById(int $id)
    {
        $query = $this->db->placehold("SELECT * FROM s_multipolis WHERE id = ?", $id);
        $this->db->query($query);

        return $this->db->result();
    }

    /**
     * Поиск мультиполисов по фильтру
     * @param array $filter_data
     * @param bool $return_all
     * @return array|false
     */
    public function selectAll(array $filter_data, bool $return_all = true)
    {
        $where = [];
        $sql = "SELECT * FROM s_multipolis WHERE 1
                 -- {{where}}";

        if (!empty($filter_data['filter_payment_id'])) {
            $where[] = $this->db->placehold("payment_id = ?", (int)$filter_data['filter_payment_id']);
        }

        if (!empty($filter_data['filter_payment_method'])) {
            $where[] = $this->db->placehold("payment_method = ?", $this->db->escape($filter_data['filter_payment_method']));
        }

        if (isset($filter_data['filter_is_sent'])) {
            $where[] = $this->db->placehold("is_sent = ?", (int)$filter_data['filter_is_sent']);
        }

        if (isset($filter_data['filter_user_id'])) {
            $where[] = $this->db->placehold("user_id = ?", (int)$filter_data['filter_user_id']);
        }

        if (isset($filter_data['filter_order_id'])) {
            $where[] = $this->db->placehold("order_id = ?", (int)$filter_data['filter_order_id']);
        }

        if (isset($filter_data['filter_status'])) {
            $where[] = $this->db->placehold("status = ?", $this->db->escape($filter_data['filter_status']));
        }

        if( isset( $filter_data['filter_refunded'] ) ){
            $where[] = $filter_data['filter_refunded']
                ? $this->db->placehold('amount = return_amount')
                : $this->db->placehold('amount > return_amount');
        }
        
        /**
         * Время жизни мультиполиса
         */
        if (isset($filter_data['filter_limit_live_days'])) {
            $where[] = $this->db->placehold("datediff(NOW(), date_added) <= ?", (int)$filter_data['filter_limit_live_days']);
        }

        $query = strtr($sql, [
            '-- {{where}}' => !empty($where) ? "AND " . implode(" AND ", $where) : '',
        ]);

        $this->db->query($query);

        if ($return_all) {
            return $this->db->results();
        } else {
            return $this->db->result();
        }
    }

    /**
     * Вовращает кол-во мультиполисов по фильтру
     * @param array $filter_data
     * @return int
     */
    public function selectTotal(array $filter_data): int
    {
        $where = [];
        $sql = "SELECT COUNT(*) as total FROM s_multipolis WHERE 1
                 -- {{where}}";

        if (!empty($filter_data['filter_user_id'])) {
            $where[] = $this->db->placehold("user_id = ?", (int)$filter_data['filter_user_id']);
        }

        $query = strtr($sql, [
            '-- {{where}}' => !empty($where) ? "AND " . implode(" AND ", $where) : '',
        ]);

        $this->db->query($query);

        return (int)$this->db->result('total');
    }

    /**
     * Добавляет запись об отправленной форме мультиполиса
     * @param array $data
     * @return mixed
     */
    public function addMultipolisForm(array $data)
    {
        $query = $this->db->placehold("INSERT INTO s_multipolis_form SET ?%", $data);
        $this->db->query($query);
        return $this->db->insert_id();
    }

    /**
     * Отправка писем для уведомления
     * @param $body_text
     * @return bool
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function sendNoticeEmail($body_text)
    {
        $email = new PHPMailer();
        $email->SetFrom('test.zay@mail.ru', 'Boostra Mail Agent');
        $email->Subject   = 'Запроса на консультацию по консьерж сервису';
        $email->isHTML();

        $email->Body  = $body_text;
        $email->AddAddress(' konsultant-b@internet.ru');
        $email->CharSet = 'UTF-8';

        $email->isSMTP();
        $email->SMTPAuth = true;
        //$email->SMTPDebug = 0;

        $email->Host = 'ssl://smtp.mail.ru';
        $email->Port = 465;
        $email->Username = $this->config->mail_mp_user_name;
        $email->Password = $this->config->mail_mp_password;

        return $email->send();
    }

    /**
     * Генерирует стоимость мультиполиса
     * @param $user
     * @return float
     * @throws Exception
     */
    public function getMultipolisAmount($user)
    {
        $payment_date = new DateTime($user->balance->payment_date);
        $payment_date->setTime(0, 0);

        $now_date_time = new DateTime();
        $now_date_time->setTime(0, 0);

        $interval_diff = $payment_date->diff($now_date_time);
        $percent_multipolis = ($interval_diff->days > 0 && !$interval_diff->invert) ? 0.32 : 0.22; // если просрочка в днях, то 32% иначе 22%
        return round($user->balance->ostatok_od * $percent_multipolis);
    }
}
