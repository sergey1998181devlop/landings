<?php

require_once 'Simpla.php';

class Organizations extends Simpla
{
    public const BOOSTRA_ID = 1;
    public const AKVARIUS_ID = 6;
    public const ACADO_ID = 7;
    public const FINTEHMARKET_ID = 8;
    public const FINLAB_ID = 11;
    public const VIPZAIM_ID = 12;
    public const LORD = 15;
    
    public function get_base_organization($params = [])
    {
        return $this->get_organization($this->get_base_organization_id($params));
    }
    
    public function get_base_organization_id($params = [])
    {
        return $this->config->default_organization_id;

//        if (isset($params['user_id']) && $this->is_test_user($params['user_id'])) {
//            return self::FINLAB_ID;
//        } else {
//            return $this->config->default_organization_id;
//        }
    }
    
    private function is_test_user($user_id) {
        $test_users = array_map('trim', explode(',', $this->settings->test_users_multiorg));
        return in_array($params['user_id'], $test_users);
    }
    
    /**
     * Organizations::get_inn_for_recurrents()
     * Метод возвращает список ИНН организаций, по которым нужно списывать реккуренты
     * Также используется для проверки наличия выданных займов в Soap1c::DebtForFIO
     * @return array
     */
    public function get_inn_for_recurrents()
    {
        $organizations_map = [
            self::BOOSTRA_ID,
            self::AKVARIUS_ID,
            self::ACADO_ID,
            self::FINLAB_ID,
            self::VIPZAIM_ID,
        ];

        $inn = [];
        foreach ($this->getList() as $org) {
            if (in_array($org->id, $organizations_map)) {
                $inn[] = $org->inn;
            }
        }
        
        return $inn;
    }

    /**
     * Get list organizations
     *
     * @return array
     */
    public function getList(): array
    {

        $query = $this->db->placehold("SELECT * FROM s_organizations ORDER BY id ASC ");

        $this->db->query($query);
        return $this->db->results();

    }

    public function get_organization($id)
    {
    	$this->db->query("
            SELECT * FROM s_organizations
            WHERE id = ?
        ", (int)$id);

        $organization = $this->db->result();

        if (!empty($organization)) {
            $organization->params = json_decode($organization->params, true);
        }

        return $organization;
    }
    
    public function get_organization_id_by_inn($inn)
    {
    	$this->db->query("
            SELECT id FROM s_organizations
            WHERE inn = ?
        ", (int)$inn);
        return $this->db->result('id');        
    }

    /**
     * Считается ли нашей (не требует перепривязки) карта с этой организации?
     * @return bool
     */
    public function is_our_card($organization_id)
    {
        return $organization_id != self::BOOSTRA_ID;
    }

    public function isFinlab(int $organizationId): bool
    {
        return $organizationId === $this->organizations::FINLAB_ID;
    }

    public function isAkvarius(int $organizationId): bool
    {
        return $organizationId === $this->organizations::AKVARIUS_ID;
    }
}
