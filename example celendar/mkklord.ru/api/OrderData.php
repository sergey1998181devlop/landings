<?php

require_once 'Simpla.php';

/**
 * Дополнительные поля для заявок
 *
 * s_order_data
 */
class OrderData extends Simpla
{
    /** @var string Данные о браузере и системе пользователя для JuicyScore */
    public const USERAGENT = 'USERAGENT';
    public const HTTP_REFERER = 'REFERER';

    /** @var string Согласие на переуступку права требования, 0 согласен, 1 не согласен */
    public const AGREE_CLAIM_VALUE = 'AGREE_CLAIM_VALUE';

    /** @var string Флаг безопасного флоу при выдаче займа, 1 безопасное, 0 опасное */
    public const SAFETY_FLOW = 'SAFETY_FLOW';

    /* WARNING: В методах get_order_by_1c, get_order и get_crm_order в api/Orders  Настройки ДОПов изменены:  0 отключен, 1 включен, чтобы во фронте было удобно обрабатывать */
    /** @var string Вита-мед при пролонгации, 1 отключен, 0 включен */
    public const ADDITIONAL_SERVICE_TV_MED = 'additional_service_tv_med';
    /** @var string Консьерж при пролонгации,1 отключен, 0 включен */
    public const ADDITIONAL_SERVICE_MULTIPOLIS = 'additional_service_multipolis';
    /** @var string Доп. услуга на частичном закрытии,1 отключен, 0 включен */
    public const ADDITIONAL_SERVICE_PARTIAL_REPAYMENT = 'additional_service_partial_repayment';
    /** @var string Доп. услуга на закрытии,1 отключен, 0 включен */

    public const ADDITIONAL_SERVICE_REPAYMENT = 'additional_service_repayment';
    /** @var string Доп. услуга при выдаче, 1 отключен, 0 включен */
    public const DISABLE_ADDITIONAL_SERVICE_ON_ISSUE = 'disable_additional_service_on_issue';


    /** @var string Был ли куплен отказником причина (узнать причину) , 1-куплен */
    public const PAYMENT_REFUSER = 'payment_refuser';

    /** @var string Доп. услуга на закрытии 50%,1 отключен, 0 включен */
    public const HALF_ADDITIONAL_SERVICE_REPAYMENT = 'half_additional_service_repayment';

    /** @var string  Доп. услуга на частичном закрытии 50%,1 отключен, 0 включен */
    public const HALF_ADDITIONAL_SERVICE_PARTIAL_REPAYMENT = 'half_additional_service_partial_repayment';

    /** @var string Звездный Оракул(SO) на закрытии,1 отключен, 0 включен */
    public const ADDITIONAL_SERVICE_SO_REPAYMENT = 'additional_service_so_repayment';
    /** @var string Звездный Оракул(SO) на закрытии 50%,1 отключен, 0 включен */
    public const HALF_ADDITIONAL_SERVICE_SO_REPAYMENT = 'half_additional_service_so_repayment';

    /** @var string Звездный Оракул(SO) на частичном закрытии,1 отключен, 0 включен */
    public const ADDITIONAL_SERVICE_SO_PARTIAL_REPAYMENT = 'additional_service_so_partial_repayment';
    /** @var string Звездный Оракул(SO) на частичном закрытии 50%,1 отключен, 0 включен */
    public const HALF_ADDITIONAL_SERVICE_SO_PARTIAL_REPAYMENT = 'half_additional_service_so_partial_repayment';

    /** @var string Доп. услуга на закрытии,0 отключен, 1 включен */
    public const ADDITIONAL_SERVICE_DEFAULT_VALUE = 1;

    /** @var string Данные от банки ру после отправленной заявки */
    public const SENT_TO_BANKI_RU = 'sent_to_banki_ru';

    /** @var string Ссылка для редиректа в Вonondo */
    public const BONONDO_CLIENT_URL = "bonondoClientUrl";
    public const BONONDO_REDIRECT_COUNT = "bonondoRedirectCount";
    
    /* @var string К одобренному займу привязали новую карту */
    public const IS_NEW_CARD_LINKED = "is_new_card_linked";

    /** @var string Запуск ```$this->leadgid->reject_actions($order_id)``` уже проводился */
    public const HAS_REJECT_ACTIONS = 'has_reject_actions';

    public const ADDITIONAL_SERVICES = [
        self::ADDITIONAL_SERVICE_TV_MED,
        self::ADDITIONAL_SERVICE_MULTIPOLIS,
        self::ADDITIONAL_SERVICE_PARTIAL_REPAYMENT,
        self::ADDITIONAL_SERVICE_REPAYMENT,
        self::HALF_ADDITIONAL_SERVICE_PARTIAL_REPAYMENT,
        self::HALF_ADDITIONAL_SERVICE_REPAYMENT,
        self::ADDITIONAL_SERVICE_SO_REPAYMENT,
        self::ADDITIONAL_SERVICE_SO_PARTIAL_REPAYMENT,
        self::HALF_ADDITIONAL_SERVICE_SO_REPAYMENT,
        self::HALF_ADDITIONAL_SERVICE_SO_PARTIAL_REPAYMENT,
    ];

    /** @var string Флаг нужно ли авто-подтверждение для авто-одобренной заявки */
    public const NEED_AUTO_CONFIRM = 'need_auto_confirm';

    /** @var string Решение по наличию у клиента самозапрета перед выдачей */
    public const SELF_DEC_DECISION = 'self_dec_decision';

    /** @var string Последний applicationId в акси, по которому проверяли, есть ли у клиента перед выдачей самозапрет */
    public const SELF_DEC_AXI_APPLICATION_ID = 'self_dec_axi_application_id';
    /** @var integer Код АСП для автоподписания */
    public const AUTOCONFIRM_ASP = 'autoconfirm_asp';
    /** @var integer Сумма запрошенная клиентом при подаче заявки*/
    public const USER_AMOUNT = 'user_amount';

    /**
     * Получение доп.поля из заявки.
     *
     * Для получения значения поля используйте `$field->value`, либо `$this->order_data->read($orderId, $key)`
     * @param int $orderId Id заявки из s_orders
     * @param string $key Ключ, по которому хранятся данные (напр. USERAGENT)
     * @return object
     */
    public function get(int $orderId, string $key)
    {
        $query = $this->db->placehold('SELECT * FROM __order_data WHERE `order_id` = ? AND `key` = ?', $orderId, $key);
        $this->db->query($query);
        return $this->db->result();
    }

    /**
     * Чтение доп.поля из заявки.
     *
     * Отличается от get тем, что возвращает строку, а не объект
     * @param int $orderId Id заявки из s_orders
     * @param string $key Ключ, по которому хранятся данные (напр. USERAGENT)
     * @return null|string
     */
    public function read(int $orderId, string $key)
    {
        $field = $this->get($orderId, $key);
        if (isset($field))
            return $field->value;
        return null;
    }

    /**
     * Получение всех доп.полей из заявки.
     *
     * Для получений значения каждого поля используйте `$field->value`, либо `$this->order_data->readAll($orderId)`
     * @param int $orderId Id заявки из s_orders
     * @return array|false
     */
    public function getAll(int $orderId)
    {
        $query = $this->db->placehold('SELECT * FROM __order_data WHERE `order_id` = ?', $orderId);
        $this->db->query($query);
        return $this->db->results();
    }

    /**
     * Чтение всех доп.полей из заявки.
     *
     * Отличается от getAll тем, что возвращает ассоциативный массив, а не список объектов
     * ```
     * $fields = $this->order_data->readAll($orderId);
     * // Результат:
     * $fields = [
     *      [KEY1] => 'Value1',
     *      [KEY2] => 'Value2',
     *      ...
     * ];```
     * @param int $orderId Id заявки из s_orders
     * @return array
     */
    public function readAll(int $orderId)
    {
        $fields = $this->getAll($orderId);
        if (empty($fields))
            return [];

        $result = [];
        foreach ($fields as $field)
            $result[$field->key] = $field->value;
        return $result;
    }

    /**
     * Запись доп.поля в заявку.
     * Можно использовать как для создания новых полей, так и для обновления существующих.
     *
     * Для удаления поля установите $value = null, либо просто не указывайте его.
     * @param int $orderId Id заявки из s_orders
     * @param string $key Ключ, по которому хранятся данные (напр. USERAGENT)
     * @param null|string $value Строковое значение или null, если вы хотите удалить запись
     * @return mixed
     */
    public function set(int $orderId, string $key, $value = null)
    {
        if (is_null($value))
            return $this->delete($orderId, $key);
        return $this->replace($orderId, $key, $value);
    }

    private function replace(int $orderId, string $key, $value)
    {
        $query = $this->db->placehold("REPLACE INTO __order_data (`order_id`, `key`, `value`) VALUES (?, ?, ?)", $orderId, $key, $value);
        return $this->db->query($query);
    }

    private function delete(int $orderId, string $key)
    {
        $query = $this->db->placehold("DELETE FROM __order_data WHERE `order_id` = ? AND `key` = ?", $orderId, $key);
        return $this->db->query($query);
    }

    /**
     * @param $orderId
     * @return array
     */
    public function getAdditionalServices($orderId): array
    {
        $query = $this->db->placehold('SELECT * FROM __order_data WHERE `order_id` = ? and `key` in (?@)', $orderId, self::ADDITIONAL_SERVICES);
        $this->db->query($query);
        $resultDb = $this->db->results() ?? [];

        $result = [];
        foreach ($resultDb as $item) {
            $result[$item->key] = $item->value;
        }
        return $result;
    }

    /**
     * Получение количества заявок с доп.полем из данных
     *
     * @param array $orderIds Массив Id заявок из s_orders
     * @param string $key Ключ, по которому хранятся данные (напр. USERAGENT)
     * @return object
     */
    public function countByKeyAndOrder(array $orderIds, string $key)
    {
        $query = $this->db->placehold('SELECT * FROM __order_data WHERE `order_id` IN (?@) AND `key` = ?', array_map('intval', (array) $orderIds), $key);
        $this->db->query($query);

        return $this->db->num_rows();
    }
}
