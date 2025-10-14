<?php
date_default_timezone_set('Europe/Moscow');

session_start();
chdir('..');

require_once 'api/Simpla.php';

class ProlongationRecurrentProcess extends Simpla
{
    private const RECURRENT_TOKEN = '45d84953a5b22baecf59d751965ef53de30e97c972a27a986ec12253d5d42298';
    private $uid;
    private $contract_number;
    private $payment_data;
    private $recurrent_token;
    private $operation_id;
    private $register_id;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        parent::__construct();

        $this->recurrent_token = $this->request->post('recurrent_token', 'string');
        if (!$this->recurrent_token || $this->recurrent_token !== self::RECURRENT_TOKEN) {
            http_response_code(403);
            echo json_encode(['error' => 'Access denied']);
            exit;
        }

        $this->uid = $this->request->post('uid', 'string');
        $this->contract_number = $this->request->post('contract_number', 'string');
        $this->operation_id = $this->request->post('operation_id', 'integer');
        $this->register_id = $this->request->post('register_id', 'integer');

        $action = $this->request->post('action', 'string');

        switch ($action) {
            case 'getDetails':
                $this->getAdditionalServicesDetails();
                break;
            case 'confirmPayment':
                $this->confirmPayment();
                break;
            default:
                echo json_encode(['error' => 'Invalid action']);
                exit;
        }
    }

    /**
     * Метод для получения платежных данных из запроса
     * @return array
     */
    private function getPaymentDataFromRequest(): array
    {
        $keys = [
            'user_id', 'order_id', 'contract_number', 'amount', 'fee',
            'sector', 'register_id', 'operation_id', 'description',
            'card_pan',
            'operation_date',
            'is_sbp',
            'callback_response',
            'body'
        ];

        return array_reduce($keys, function ($carry, $key) {
            $carry[$key] = $this->request->post($key);
            return $carry;
        }, []);
    }

    /**
     * Метод для подготовки платежных данных
     * @param $user
     * @param $order
     * @param $organizationId
     * @return array
     */
    private function preparePaymentData($user, $order, $organizationId): array
    {
        return [
            'user_id' => $this->payment_data['user_id'],
            'order_id' => $this->payment_data['order_id'],
            'contract_number' => $this->payment_data['contract_number'],
            'card_id' => $order->card_id,
            'amount' => $this->payment_data['amount'],
            'insure' => 0,
            'fee' => $this->payment_data['fee'] ?? 0,
            'prolongation' => 1,
            'asp' => '',
            'created' => date('Y-m-d H:i:s'),
            'sector' => $this->payment_data['sector'],
            'register_id' => $this->payment_data['register_id'],
            'operation_id' => $this->payment_data['operation_id'],
            'description' => $this->payment_data['description'],
            'payment_type' => 'debt',
            'calc_percents' => 0,
            'grace_payment' => 0,
            'card_pan' => $this->payment_data['card_pan'],
            'operation_date' => $this->payment_data['operation_date'],
            'body' => serialize($this->payment_data['body']),
            'callback_response' => serialize($this->payment_data['callback_response']),
            'chdp' => 0,
            'pdp' => 0,
            'organization_id' => $organizationId,
            'contract_payment' => 0,
            'is_sbp' => $this->payment_data['is_sbp'],
            'refinance' => 0,
            'create_from' => 'recurrent',
        ];
    }

    /**
     * Метод для получения баланса юзера из 1С
     * @return object
     */
    private function getUserBalance(): ?object
    {
        for ($i = 0; $i < 2; $i++) {
            $response_balances = $this->soap->get_user_balances_array_1c($this->uid);

            if (is_array($response_balances) && (!isset($response_balances['errors']) || !$response_balances['errors'])) {
                $order_balance = array_filter($response_balances, fn($item) => $item['НомерЗайма'] === $this->contract_number);
                if (!empty($order_balance)) {
                    return (object)array_shift($order_balance);
                }
            }

            sleep(1); // пауза для повторного обращения к 1С, если произошла ошибка
        }

        $this->sendError('Order not found in 1c');

        return null;
    }

    /**
     * Метод для проверки доступности пролонгации
     */
    private function isProlongationAvailable($balance_1c): bool
    {
        if (empty($balance_1c->СуммаДляПролонгации) || $balance_1c->СуммаДляПролонгации <= 0) {
            $this->sendError('Prolongation is not available to the client');
            return false;
        }
        return true;
    }

    /**
     * Метод валидации входных данных
     */
    private function validateRequest(): bool
    {
        if (!$this->uid || !$this->contract_number || !$this->register_id || !$this->operation_id) {
            $this->sendError('UID,contract,register_id or operation_id number missing');
            return false;
        }
        return true;
    }

    /**
     * Метод для получения подробной информации о доп услугах
     * @return void
     * @throws Exception
     */
    private function getAdditionalServicesDetails(): void
    {
        if (!$this->uid || !$this->contract_number) {
            $this->sendError('UID or contract is missing');
            return;
        }

        $user = $this->users->get_user_by_uid($this->uid);
        $balance_1c = $this->getUserBalance();

        if (!$this->isProlongationAvailable($balance_1c)) {
            return;
        }

        $order = $this->orders->get_order_by_1c($balance_1c->Заявка);
        $user->balance = $this->users->make_up_user_balance($user->id, $balance_1c);
        $user->balance->calc_percents = $this->users->calc_percents($user->balance);
        $user->order = (array)$order;

        $organizationId = $this->organizations->get_organization_id_by_inn($balance_1c->ИННТекущейОрганизации);

        $response = [
            'concierge' => $this->getConciergeDetails($user, $organizationId),
            'vitamed' => $this->getVitaMedDetails($organizationId),
        ];

        $this->sendResponse($response);
    }

    /**
     * Метод для подтверждения оплаты
     * @return void
     */
    private function confirmPayment(): void
    {
        if (!$this->validateRequest()) {
            return;
        }

        try {
            $this->payment_data = $this->getPaymentDataFromRequest();

            if ($payment = $this->best2pay->get_register_id_payment($this->payment_data['register_id'])) {
                $response = ['success' => true];

                $multipolis = $this->multipolis->selectAll(['filter_payment_id' => $payment->id], false);
                if ($multipolis) {
                    $response['concierge'] = [
                        'СуммаСтраховки' => $multipolis->amount,
                        'НомерСтраховки' => $multipolis->number,
                        'Organization' => $multipolis->organization_id,
                    ];
                }

                $vita_med = $this->tv_medical->selectPayments(['filter_payment_id' => $payment->id], false);
                if ($vita_med) {
                    $response['vitamed'] = [
                        'ID_ВитаМед' => $vita_med->tv_medical_id,
                        'Сумма' => $vita_med->amount,
                        'НомерПолиса' => $vita_med->id,
                        'insurer' => '',
                        'Organization' => $vita_med->organization_id,
                    ];
                }
                $this->sendResponse($response);
            }

            if (empty($this->payment_data['order_id'])) {
                $this->sendError('Order ID is missing or empty');
                return;
            }

            if (!isset($this->payment_data['amount']) || !is_numeric($this->payment_data['amount']) || (float)$this->payment_data['amount'] <= 0) {
                $this->sendError('Amount is missing, not numeric, or less than or equal to zero');
                return;
            }

            $order = $this->orders->get_crm_order($this->payment_data['order_id']);
            if (!$order) {
                $this->sendError('Order not found');
                return;
            }
            $user = $this->users->get_user((int)$order->user_id);
            if (!$user) {
                $this->sendError('User not found');
                return;
            }
            $balance_1c = $this->getUserBalance();

            if (!$this->isProlongationAvailable($balance_1c)) {
                return;
            }

            $user->balance = $this->users->make_up_user_balance($this->payment_data['user_id'], $balance_1c);
            $user->balance->calc_percents = $this->users->calc_percents($user->balance);
            $user->order = (array)$order;

            $vita_med_tariffs = $this->tv_medical->getAllVitaMedPrices();
            $active_vita_med = $vita_med_tariffs[1] ?? null;
            $concierge_amount = $this->multipolis->getMultipolisAmount($user);
            $vita_med_amount = $active_vita_med ? $active_vita_med->price : 0;

            $organizationId = $this->organizations->get_organization_id_by_inn($balance_1c->ИННТекущейОрганизации);
            $order = $this->orders->get_order_by_1c($balance_1c->Заявка);

            if (!is_array($this->payment_data)) {
                echo json_encode(['error' => 'Invalid payment data']);
                exit;
            }

            $payment_data = $this->preparePaymentData($user, $order, $organizationId);

            if (empty($payment_data['amount']) || (float)$payment_data['amount'] <= 0) {
                $this->sendError('Calculated payment amount is invalid');
                return;
            }

            $payment_id = $this->best2pay->add_payment($payment_data);

            if (!$payment_id) {
                $response['error'] = 'Payment creation failed';
                echo json_encode($response);
                exit;
            }

            $response = ['success' => true];

            // Обработка мультиполиса (консьержа)
            if ($concierge_amount > 0) {
                $concierge_data = [
                    'user_id' => $payment_data['user_id'],
                    'order_id' => $payment_data['order_id'],
                    'status' => $this->multipolis::STATUS_NEW,
                    'amount' => $concierge_amount,
                    'payment_method' => $this->orders::PAYMENT_METHOD_B2P,
                    'payment_id' => (int)$payment_id,
                    'organization_id' => $payment_data['organization_id'],
                ];

                $concierge_id = $this->multipolis->addItem($concierge_data);
                $concierge = $this->multipolis->selectItemById($concierge_id);

                $response['concierge'] = [
                    'СуммаСтраховки' => $concierge_data['amount'],
                    'НомерСтраховки' => $concierge->number,
                    'Organization' => $concierge_data['organization_id'],
                ];

                $multipolis_key = $this->dop_license->createLicenseWithKey(
                    $this->dop_license::SERVICE_CONCIERGE,
                    [
                        'user_id' => $concierge_data['user_id'],
                        'order_id' => $concierge_data['order_id'],
                        'service_id' => $concierge_id,
                        'organization_id' => $concierge_data['organization_id'],
                        'amount' => $concierge_data['amount'],
                    ]
                );

                $this->createReceiptForConcierge($concierge);
                $this->createConciergeDocuments($concierge, $multipolis_key);
            }

            // Обработка телемедицины (витамед)
            if ($active_vita_med->price > 0) {
                $vita_med_data = [
                    'tv_medical_id' => $active_vita_med->id,
                    'amount' => $vita_med_amount,
                    'user_id' => $payment_data['user_id'],
                    'order_id' => $payment_data['order_id'],
                    'status' => $this->tv_medical::TV_MEDICAL_PAYMENT_STATUS_NEW,
                    'payment_method' => $this->orders::PAYMENT_METHOD_B2P,
                    'payment_id' => (int)$payment_id,
                    'organization_id' => $payment_data['organization_id'],
                ];

                $vita_med_id = $this->tv_medical->addPayment($vita_med_data);
                $vita_med = $this->tv_medical->getTvMedicalById($vita_med_id);

                $response['vitamed'] = [
                    'ID_ВитаМед' => $vita_med_data['tv_medical_id'],
                    'Сумма' => $vita_med_data['amount'],
                    'НомерПолиса' => $vita_med_id,
                    'insurer' => '',
                    'Organization' => $vita_med_data['organization_id'],
                ];

                $tvmed_key = $this->dop_license->createLicenseWithKey(
                    $this->dop_license::SERVICE_VITAMED,
                    [
                        'user_id' => $vita_med_data['user_id'],
                        'order_id' => $vita_med_data['order_id'],
                        'service_id' => $vita_med_id,
                        'organization_id' => $vita_med_data['organization_id'],
                        'amount' => $vita_med_amount,
                    ]
                );

                $this->createReceiptForVitaMed($vita_med);
                $this->createVitaMedDocuments($vita_med, $tvmed_key);
            }

            $update = [
                'reason_code' => 1,
                'reference' => $payment_id,
            ];

            $this->best2pay->update_payment($payment_id, $update);

            $this->sendResponse($response);
        } catch (Exception $e) {
            $this->sendError('Server error: ' . $e->getMessage());
        }
    }

    /**
     * Метод для создания чека консьержа
     * @param $concierge
     * @return void
     */
    private function createReceiptForConcierge($concierge): void
    {
        $receipt_data = [
            'user_id' => $concierge->user_id,
            'order_id' => $concierge->order_id,
            'amount' => $concierge->amount,
            'payment_id' => $concierge->payment_id,
            'payment_method' => $this->orders::PAYMENT_METHOD_B2P,
            'payment_type' => $this->receipts::PAYMENT_TYPE_MULTIPOLIS,
            'organization_id' => $concierge->organization_id,
            'description' => 'ПО «Boostra Concierge»',
        ];

        $this->receipts->addItem($receipt_data);

        $this->multipolis->updateItem((int)$concierge->id, [
            'status' => $this->multipolis::STATUS_SUCCESS,
        ]);
    }

    /**
     * Метод для создания чека витамед
     * @param $vita_med
     * @return void
     */
    private function createReceiptForVitaMed($vita_med): void
    {
        $receipt_data = [
            'user_id' => $vita_med->user_id,
            'order_id' => $vita_med->order_id,
            'amount' => $vita_med->amount,
            'payment_id' => $vita_med->payment_id,
            'payment_method' => $this->orders::PAYMENT_METHOD_B2P,
            'payment_type' => $this->receipts::PAYMENT_TYPE_TV_MEDICAL,
            'organization_id' => $vita_med->organization_id,
            'description' => 'ПО «ВитаМед»',
        ];

        $this->receipts->addItem($receipt_data);

        $this->tv_medical->updatePayment((int)$vita_med->id, [
            'status' => $this->tv_medical::TV_MEDICAL_PAYMENT_STATUS_SUCCESS,
            'sent_to_api' => 1,
        ]);
    }

    /**
     * Метод для создания документа консьержа
     * @param $concierge
     * @param $multipolis_key
     * @return void
     */
    private function createConciergeDocuments($concierge, $multipolis_key): void
    {
        $user = $this->users->get_user((int)$concierge->user_id);
        $clear_passport_serial = preg_replace('/[^0-9]/', '', $user->passport_serial);
        $passport_serial = substr($clear_passport_serial, 0, 4);
        $passport_number = substr($clear_passport_serial, 4);

        $params = [
            'multipolis_number' => $concierge->number,
            'lastname' => $user->lastname,
            'firstname' => $user->firstname,
            'patronymic' => $user->patronymic,
            'birth' => $user->birth,
            'gender' => $user->gender === 'male' ? 'Мужской' : 'Женский',
            'phone_mobile' => $user->phone_mobile,
            'passport_serial' => $passport_serial,
            'passport_number' => $passport_number,
            'passport_original' => $user->passport_serial,
            'order_date_end' => date('Y-m-d H:i:s'),
            'amount' => $concierge->amount,
            'pay_date' => date('Y-m-d H:i:s'),
            'payment_id' => $concierge->payment_id,
            'license_key' => $multipolis_key,
        ];

        $this->documents->create_document(
            [
                'type' => $this->documents::DOC_MULTIPOLIS,
                'user_id' => $concierge->user_id,
                'order_id' => $concierge->order_id,
                'contract_number' => $this->contract_number,
                'params' => $params,
            ]
        );
    }

    /**
     * Метод для создания документа витамед
     * @param $vita_med
     * @param $tvmed_key
     * @return void
     */
    private function createVitaMedDocuments($vita_med, $tvmed_key): void
    {
        $user = $this->users->get_user((int)$vita_med->user_id);
        $payment = $this->best2pay->get_payment($vita_med->payment_id);
        $payment->tv_medical = $vita_med;

        $this->tv_medical->generatePayDocs($user, $payment, (int)$vita_med->order_id, null, $tvmed_key);
    }

    /**
     * Данные о сервисе консьерж
     * @throws Exception
     */
    private function getConciergeDetails($user, int $organizationId): array
    {
        return [
            'СуммаСтраховки' => $this->multipolis->getMultipolisAmount($user),
            'Organization' => $organizationId,
        ];
    }

    /**
     * Данные о сервисе витамед
     */
    private function getVitaMedDetails(int $organizationId): array
    {
        $vita_med_tariffs = $this->tv_medical->getAllVitaMedPrices();
        $active_vita_med = $vita_med_tariffs[1] ?? null;

        return [
            'Сумма' => $active_vita_med ? $active_vita_med->price : 0,
            'Organization' => $organizationId,
        ];
    }

    /**
     * Метод отправки ошибки
     */
    private function sendError(string $message): void
    {
        $this->logging(__METHOD__, 'error', $_POST, '', 'recurrent_payment.txt');
        echo json_encode(['error' => $message]);
        exit;
    }

    /**
     * Метод отправки успешного ответа
     */
    private function sendResponse(array $data): void
    {
        echo json_encode($data);
        exit;
    }
}

new ProlongationRecurrentProcess();
