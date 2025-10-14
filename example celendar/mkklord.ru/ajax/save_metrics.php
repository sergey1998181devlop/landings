<?php

error_reporting(-1);
ini_set('display_errors', 'On');

session_start();

require_once '../api/Simpla.php';

class SaveMetrics extends Simpla
{
    public function __construct()
    {
    	parent::__construct();
        
        $this->run();
    }
    
    private function run()
    {
        $type = $this->request->get('type');
        $action = $this->request->get('action');
        $ip = $_SERVER['REMOTE_ADDR'];
        $user_id = $_SESSION['user_id'];
        
        $this->db->query("
            INSERT INTO yametric_logs
            SET ?%
        ", [
            'ya_type' => $this->request->get('type'),
            'ya_action' => $this->request->get('action'),
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_id' => $_SESSION['user_id'] ?? null,
            'visit_id' => $_SESSION['vid'] ?? null,
            'created' => date('Y-m-d H:i:s'),
        ]);
    }
}
new SaveMetrics();