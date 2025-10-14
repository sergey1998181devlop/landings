<?php

require_once 'View.php';

/**
 * Модуль контроллер ГосУслуги
 */
class TBankIdView extends View
{
    private array $errors = [];

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws SoapFault
     */
    public function fetch()
    {
        $action = trim($this->request->get('action', 'string'));

        /**
         * @uses auth
         */
        if (method_exists($this, $action)) {
            $this->{$action}();
        }

        $this->request->redirect('/init_user');
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function auth()
    {
        $this->validateState();
        $code = trim($this->request->get('code'));
        $response_token = $this->TBankIdService->getToken($code);

        if (empty($response_token['access_token'])) {
            throw new Exception("Bad request", 400);
        }

        $this->TBankIdService->setUserToken($response_token['access_token']);

        $mainData = $this->TBankIdService->getMainData();
        $inn = $this->TBankIdService->getInn();
        $addresses = $this->TBankIdService->getAddresses();
        $passport_data = $this->TBankIdService->getPassportData();


        if(empty($mainData['phone_number'])){
            $this->request->redirect('/');
        }

        $amount = $_COOKIE['amount'] ?? 30000;
        $period = $_COOKIE['period'] ?? 16;

        $sub = $mainData['sub'];

        $user = new StdClass();

        if (empty($mainData['family_name']) || empty($passport_data['serialNumber']) || empty($addresses)) {
            $errors = [];

            if (empty($mainData['family_name'])) {
                $errors[] = "ФИО";
            }

            if (empty($passport_data['serialNumber'])) {
                $errors[] = "Паспорт";
            }

            if (empty($addresses)) {
                $errors[] = "Адрес";
            }

            $_SESSION['t_id_error'] = "Данных из сервиса T-ID недостаточно для моментальной регистрации. Пожалуйста, попробуйте снова, отметив обязательные пункты (" . implode(',', $errors) . ").";
            return;
        }

        $user->firstname = $mainData['given_name'];
        $user->lastname = $mainData['family_name'];
        $user->patronymic = $mainData['middle_name'];
        $user->phone_mobile = $this->users->clear_phone($mainData['phone_number']);
        $user->birth = (new DateTime($mainData['birthdate']))->format('d.m.Y');

        $user->first_loan_period = min($period, Orders::MAX_PERIOD_FIRST_LOAN);
        $user->first_loan_amount = min($amount, 30000);
        $user->first_loan = 1;

        $user->reg_ip = $_SERVER['REMOTE_ADDR'];
        $user->enabled = 1;

        $user->additional_data_added = 0;
        $user->accept_data_added = 0;
        $user->files_added = 0;
        $user->card_added = 0;
        $user->created = date('Y-m-d H:i:s');
        $user->missing_real_date   = date('Y-m-d H:i:s');

        $user->service_sms = 1;
        $user->service_insurance = 0;
        $user->sms = rand(1000, 9999);

        $user->utm_source = empty($_COOKIE["utm_source"]) ? 'Boostra' : strip_tags($_COOKIE["utm_source"]);
        $user->utm_medium = empty($_COOKIE["utm_medium"]) ? 'Site' : strip_tags($_COOKIE["utm_medium"]);
        $user->utm_campaign = empty($_COOKIE["utm_campaign"]) ? 'C1_main' : strip_tags($_COOKIE["utm_campaign"]);
        $user->utm_content = empty($_COOKIE["utm_content"]) ? '' : strip_tags($_COOKIE["utm_content"]);
        $user->utm_term = empty($_COOKIE["utm_term"]) ? '' : strip_tags($_COOKIE["utm_term"]);
        $user->webmaster_id = empty($_COOKIE["webmaster_id"]) ? '' : strip_tags($_COOKIE["webmaster_id"]);
        $user->click_hash = empty($_COOKIE["click_hash"]) ? '' : strip_tags($_COOKIE["click_hash"]);

        $user->inn = $inn['inn'] ?? '';

        $validateUserPhone = $this->users->validateUserByPhone($user->phone_mobile);

        if (empty($validateUserPhone['result'])) {
            $this->request->redirect('/user/login');
        }

        if (!empty($passport_data['serialNumber'])) {
            list ($passport_serial, $passport_number) = \api\helpers\UserHelper::unserializePassport($passport_data['serialNumber']);
            $user->personal_data_added = 1;

            $user->passport_issued = $passport_data['unitName'] ?? '';
            $user->passport_serial = $passport_serial . ' ' . $passport_number;
            $user->subdivision_code = $passport_data['unitCode'] ?? '';
            $user->passport_date = (new DateTime($passport_data['issueDate']))->format('d.m.Y');
        }

        if (!empty($addresses)) {
            foreach ($addresses as $address) {
                if ($address['addressType'] === 'REGISTRATION_ADDRESS') {
                    $user->Regindex = $address['addresses'][0]['zipCode'];
                    $user->Regregion = $address['addresses'][0]['region'];
                    $user->Regcity = $address['addresses'][0]['city'];
                    $user->Regstreet = $address['addresses'][0]['street'];
                    $user->Reghousing = $address['addresses'][0]['house'];
                    $user->Regbuilding = '';
                    $user->Regroom = $address['addresses'][0]['apartment'];
                    $user->Regregion_shorttype = '';
                    $user->Regcity_shorttype = '';
                    $user->Regstreet_shorttype = '';
                }

                if ($address['addressType'] === 'RESIDENCE_ADDRESS') {
                    $user->Faktindex = $address['addresses'][0]['zipCode'];
                    $user->Faktregion = $address['addresses'][0]['region'];
                    $user->Faktcity = $address['addresses'][0]['city'];
                    $user->Faktstreet = $address['addresses'][0]['street'];
                    $user->Fakthousing = $address['addresses'][0]['house'];
                    $user->Faktbuilding = $address['addresses'][0]['apartment'];
                    $user->Faktroom = '';
                    $user->Faktregion_shorttype = '';
                    $user->Faktcity_shorttype = '';
                    $user->Faktstreet_shorttype = '';
                }
            }

            if (!empty($user->Regindex) && !empty($user->Faktindex)) {
                $user->address_data_added = 1;
            }
        }

        if ($user_id = $this->users->add_user($user))
        {
            $this->TBankId->saveSubId($user_id, $sub);

            setcookie('t_id_state', null, time() - 1, '/');

            $scorings = array_merge(Scorings::getScoringListAfterPersonalData(), Scorings::getScoringListAfterNewOrder());
            foreach ($scorings as $scoring) {
                $this->scorings->add_scoring(
                    [
                        'user_id' => $user_id,
                        'type' => $scoring,
                    ]
                );
            }

            setcookie('is_tbank_id', '1', time() + 3600);
            setcookie('amount', null);
            setcookie('period', null);

            $_SESSION['user_id'] = $user_id;
            \api\helpers\UserHelper::getJWTToken($this->config->jwt_secret_key, $user_id, 'auth_jwt_token', $this->config->jwt_expiration_time, true);
            $this->users->initAutoConfirmNewUser($user_id, $user);

            unset($_SESSION['t_id_error']);
            $this->request->redirect($this->config->root_url . '/account');
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    private function validateState()
    {
        $state = $this->request->get('state', 'string');
        if (!$this->TBankIdService->validateState($state)) {
            throw new Exception("Bad request", 400);
        }
    }
}
