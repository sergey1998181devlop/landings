<?php

require_once 'Simpla.php';

/**
 * Дополнительные поля для заявок
 *
 * s_user_data
 */
class UserData extends Simpla
{

    const SHOW_EXTRA_DOCS = 'show_extra_docs';

    /** @var string Старый номер телефона (до смены номера телефона) */
    public const OLD_PHONES = 'old_phones';

    /** @var string Решение по IDX принадлежит ли номер телефона клиенту */
    public const IDX_DECISION = 'idx_decision';

    /** @var string Последний applicationId в акси, по которому проверяли IDX (сверка номера телефона с ФИО + дата рождения)  */
    public const IDX_DECISION_AXI_APPLICATION_ID = 'idx_decision_axi_application_id';

    /** @var string Кол-во попыток сменить номер телефона  */
    public const ATTEMPTS_AMOUNT_TO_CHANGE_PHONE = 'attempts_amount_to_change_phone';

    /** @var string Тестовый клиент*/
    public const TEST_USER = 'test_user';

    /** Новый пользователь идёт по автовыдачи */
    public const AUTOCONFIRM_FLOW = 'autoconfirm_flow';

    /**
     * Получение доп.поля из заявки.
     *
     * Для получения значения поля используйте `$field->value`, либо `$this->user_data->read($userId, $key)`
     * @param int $userId Id заявки из s_users
     * @param string $key Ключ, по которому хранятся данные (напр. USERAGENT)
     * @return object
     */
    public function get(?int $userId, string $key)
    {
        $query = $this->db->placehold('SELECT * FROM __user_data WHERE `user_id` = ? AND `key` = ?', $userId, $key);
        $this->db->query($query);
        return $this->db->result();
    }

    /**
     * Чтение доп.поля из заявки.
     *
     * Отличается от get тем, что возвращает строку, а не объект
     * @param int $userId Id заявки из s_users
     * @param string $key Ключ, по которому хранятся данные (напр. USERAGENT)
     * @return null|string
     */
    public function read(?int $userId, string $key)
    {
        $field = $this->get($userId, $key);
        if (isset($field))
            return $field->value;
        return null;
    }

    /**
     * Получение всех доп.полей из заявки.
     *
     * Для получений значения каждого поля используйте `$field->value`, либо `$this->user_data->readAll($userId)`
     * @param int|array $usersId Id заявок(-ки) из s_users
     * @return array|false
     */
    public function getAll($usersId)
    {
        if (!is_array($usersId)) {
            $usersId = [$usersId];
        }

        $query = $this->db->placehold('SELECT * FROM __user_data WHERE `user_id` IN (?@)', $usersId);
        $this->db->query($query);
        return $this->db->results();
    }

    /**
     * Чтение всех доп.полей из заявки.
     *
     * Отличается от getAll тем, что возвращает ассоциативный массив, а не список объектов
     * ```
     * $fields = $this->user_data->readAll($userId);
     * // Результат:
     * $fields = [
     *      [KEY1] => 'Value1',
     *      [KEY2] => 'Value2',
     *      ...
     * ];```
     * @param int $userId Id заявки из s_users
     * @return array
     */
    public function readAll(int $userId)
    {
        $fields = $this->getAll($userId);
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
     * @param int $userId Id заявки из s_users
     * @param string $key Ключ, по которому хранятся данные (напр. USERAGENT)
     * @param null|string $value Строковое значение или null, если вы хотите удалить запись
     * @return mixed
     */
    public function set(int $userId, string $key, $value = null)
    {
        if (is_null($value))
            return $this->delete($userId, $key);
        return $this->replace($userId, $key, $value);
    }

    private function replace(int $userId, string $key, $value)
    {
        $query = $this->db->placehold("REPLACE INTO __user_data (`user_id`, `key`, `value`) VALUES (?, ?, ?)", $userId, $key, $value);
        return $this->db->query($query);
    }

    private function delete(int $userId, string $key)
    {
        $query = $this->db->placehold("DELETE FROM __user_data WHERE `user_id` = ? AND `key` = ?", $userId, $key);
        return $this->db->query($query);
    }

    public function isTestUser(int $userId): bool
    {
        return (bool)$this->user_data->read($userId, 'test_user');
    }
}