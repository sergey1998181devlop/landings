<?PHP

require_once('View.php');

class InitUserView extends View
{
	public function fetch()
	{
        if (!empty($this->user->id)) {
            header('Location: ' . $this->config->root_url . '/user');
            exit;
        }

        $_SESSION['sms_count'] = 0;

        $user_phone = $_SESSION['user_info']['phone_number'] ?? '';
        $calc_amount = $this->request->get('amount');
        $calc_period = $this->request->get('period');

        $this->design->assign('body_class', 'bg-white max-h');
        $this->design->assign('calc_amount', $calc_amount);
        $this->design->assign('calc_period', $calc_period);
        $this->design->assign('user_phone', $user_phone);

        $t_bank_button_registration_access = $this->checkTBankShowButton();

        if ($t_bank_button_registration_access) {
            $this->design->assign('t_id_state', $this->TBankIdService->setState());
            $this->design->assign('t_id_redirect_url', $this->config->root_url . '/t-bank-id/auth');
            $this->design->assign('t_bank_button_registration_access', true);
            $this->TBankIdService->setCookie();
            $this->design->assign('t_id_error', $_SESSION['t_id_error']);
        }

        unset($_SESSION['t_id_error']);

        return $this->design->fetch('init_user.tpl');
	}

    /**
     * @return bool
     */
    private function checkTBankShowButton(): bool
    {
        $utm_source = trim($_COOKIE['utm_source'] ?? '');
        if (in_array($utm_source, array_map('trim', $this->settings->t_bank_button_registration['utm_sources'] ?? []))) {
            $utm_source_valid = true;
        }

        return !empty($this->settings->t_bank_button_registration['status']) && !empty($utm_source_valid);
    }
}
