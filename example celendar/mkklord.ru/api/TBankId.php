<?php

require_once 'Simpla.php';

/**
 * Класс для работы с БД и TBankId
 */
class TBankId extends Simpla
{
    /**
     * Сохраняет для свертки sub
     * @param int $user_id
     * @param string $sub
     * @return mixed
     */
   public function saveSubId(int $user_id, string $sub)
   {
       $query = $this->db->placehold("INSERT INTO __tbank_id SET user_id = ?, sub = ?", $user_id, $sub);

       $this->db->query($query);
       return $this->db->insert_id();
   }
}
