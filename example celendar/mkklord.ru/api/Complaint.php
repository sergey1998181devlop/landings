<?php

require_once 'Simpla.php';

class Complaint extends Simpla
{
    public function add_complaint($complaint)
    {
        $query = $this->db->placehold('INSERT INTO __complaint SET ?%', $complaint);
        if (!$this->db->query($query)) {
            return false;
        }

        return $this->db->insert_id();
    }

    public function update_complaint($id, $complaint)
    {
        $query = $this->db->placehold("
            UPDATE __complaint SET ?% WHERE id = ?
        ", $complaint, (int) $id);

        $this->db->query($query);

        return $id;
    }

    public function get_limit($name, $phone, $email, $birth)
    {
        $query = $this->db->placehold(" SELECT COUNT(DISTINCT id) as count, MAX(created) as created
									    FROM __complaint
									    WHERE fio = ? AND phone = ? AND email = ? AND birth = ?",
									$name, $phone, $email, $birth);

        $this->db->query($query);

        return $this->db->result();
    }
}