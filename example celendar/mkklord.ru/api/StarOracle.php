<?php

require_once 'Simpla.php';

class StarOracle extends Simpla
{
    public const AMOUNT = 350;

    /**
     * Статус новой записи о SO
     */
    public const STAR_ORACLE_STATUS_NEW = 'NEW';

    /**
     * Статус оплаченного SO
     */
    public const STAR_ORACLE_STATUS_SUCCESS = 'SUCCESS';

    public const ACTION_TYPE_PROLONGATION = 'prolongation';
    public const ACTION_TYPE_PARTIAL_PAYMENT = 'partial_payment';
    public const ACTION_TYPE_FULL_PAYMENT = 'full_payment';
    public const ACTION_TYPE_ISSUANCE = 'issuance';

    public const ACTION_TYPE_PAYMENT = [
        self::ACTION_TYPE_FULL_PAYMENT,
        self::ACTION_TYPE_PARTIAL_PAYMENT,
        self::ACTION_TYPE_PROLONGATION,
    ];
    
    /**
     * Получает КД
     * @param int $order_id
     * @param int $user_id
     * @param string $status
     * @return false|int
     */
    public function getStarOracle(int $order_id, int $user_id, string $status = '')
    {
        $sql = "SELECT * FROM s_star_oracle WHERE order_id = ? AND user_id = ? ";

        if (!empty($status)) {
            $sql .= $this->db->placehold(" AND status = ?", $status);
        }

        $query = $this->db->placehold($sql, $order_id, $user_id);
        $this->db->query($query);
        return $this->db->result();
    }
    
    
    /**
     * Добавляет информацию о SO к пользователю
     * @param array $data
     * @return mixed
     */
    public function addStarOracleData(array $data)
    {
        $query = $this->db->placehold("INSERT INTO __star_oracle SET ?%", $data);
        $this->db->query($query);
        return $this->db->insert_id();
    }

    /**
     * Обновляет информацию о SO пользователя
     * @param int $id
     * @param array $data
     * @return void
     */
    public function updateStarOracleData(int $id, array $data)
    {
        $query = $this->db->placehold("UPDATE __star_oracle SET ?% WHERE id = ?", $data, $id);
        $this->db->query($query);
    }

    /**
     * Удаляет запись о SO
     * @param int $id
     * @return void
     */
    public function deleteStarOracle(int $id)
    {
        $query = $this->db->placehold("DELETE FROM __star_oracle WHERE id = ?", $id);
        $this->db->query($query);
    }

    /**
     * Получает тариф исходя из суммы займа
     *
     * @param int $amount
     * @param bool $is_new_client
     * @return false|int
     */
    public function getStarOraclePrice(int $amount, bool $is_new_client = true)
    {
        $query = $this->db->placehold(
            'SELECT id, price FROM __star_oracle_conditions WHERE is_new = ? AND ? BETWEEN from_amount AND to_amount ORDER BY to_amount ASC LIMIT 1',
            $is_new_client,
            $amount
        );
        $this->db->query($query);
        return $this->db->result();
    }

    /**
     * Получает все тарифы
     * @return array|false
     */
    public function getAllTariffs()
    {
        $query = "SELECT * FROM __star_oracle_conditions";
        $this->db->query($query);

        return $this->db->results();
    }

    /**
     * Поиск звездного оракла по фильтру
     * @param array $filter_data
     * @param bool $return_all
     * @return array|false
     */
    public function selectAll(array $filter_data, bool $return_all = true)
    {
        $where = [];
        $sql = "SELECT * FROM s_star_oracle WHERE 1
                 -- {{where}}";

        if (!empty($filter_data['filter_transaction_id'])) {
            $where[] = $this->db->placehold("transaction_id = ?", (int)$filter_data['filter_transaction_id']);
        }

        if (!empty($filter_data['filter_payment_method'])) {
            $where[] = $this->db->placehold("payment_method = ?", $this->db->escape($filter_data['filter_payment_method']));
        }

        if (!empty($filter_data['filter_action_type'])) {
            $where[] = $this->db->placehold("action_type IN (?@)", (array)$filter_data['filter_action_type']);
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

        $query = strtr($sql, [
            '-- {{where}}' => !empty($where) ? "AND " . implode(" AND ", $where) : '',
        ]);

        $this->db->query($query);

        if ($return_all) {
            return $this->db->results();
        }

        return $this->db->result();
    }


}
