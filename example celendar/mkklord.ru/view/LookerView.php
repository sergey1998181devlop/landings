<?php

require_once('View.php');

class LookerView extends View
{
    public function fetch()
    {
        $user_id = $this->request->get('id', 'integer');
        $hash = $this->request->get('hash');

    	$ip = $_SERVER['REMOTE_ADDR'];
        $date = date('Ymd');
        $salt = $this->settings->looker_salt;
        
        $sha1 = sha1(md5($date.$user_id.$salt).$salt);
        
        if ($sha1 != $hash)
            return false;
        
        $_SESSION['user_id'] = $user_id;
        $_SESSION['looker_mode'] = 1;

        setcookie('auth_jwt_token', null, time()-1, '/');
        $_COOKIE['auth_jwt_token'] = null;
        \api\helpers\UserHelper::getJWTToken($this->config->jwt_secret_key, $user_id, 'auth_jwt_token', $this->config->jwt_expiration_time, true);
        
        header('Location:/user');
        exit;
        
    }
    
}