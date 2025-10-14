<?php

error_reporting(-1);
ini_set('display_errors', 'On');

chdir('..');
require_once 'api/Simpla.php';
require_once dirname(__DIR__) . '/api/addons/FinancialDoctorApi.php';
session_start();

class GenerateFDKey extends Simpla
{
    public function processRequest()
    {
        $orderId = $this->request->post('order_id') ?? $this->request->get('order_id');
        $fullPaymentDone = $this->request->post('full_payment_amount_done') ?? null;

        if (!$orderId) {
            $this->jsonResponse(false, 'Не указан order_id');
            return;
        }

        if (!$order = $this->orders->get_order($orderId)) {
            $this->jsonResponse(false, 'Заказ не найден');
            return;
        }

        $userId = $order->user_id;

        $user = $this->users->get_user_by_id(intval($userId));
        if (!$user || empty($user->phone_mobile)) {
            $this->jsonResponse(false, 'Пользователь не найден или отсутствует телефон');
            return;
        }

        $creditRecords = $this->credit_doctor->getAllSuccessCreditDoctorRecordsByUserId($userId);
        $hasActiveService = false;

        foreach ($creditRecords as $record) {
            if ($record->amount_total_returned < $record->amount) {
                $hasActiveService = true;
                $orderId = $orderId ?: $record->order_id;
                break;
            }
        }

        if (!$hasActiveService && empty($fullPaymentDone)) {
            $this->jsonResponse(false, 'Услуга Финансовый доктор не куплена или уже возвращены средства');
            return;
        }

        if (!$contract = $this->contracts->get_contract($order->contract_id)) {
            $this->jsonResponse(false, 'Контракт не найден');
            return;
        }

        if ($this->credit_doctor->getLicenseByOrderId($orderId)) {
            $this->jsonResponse(false, 'Лицензия уже сгенерирована');
            return;
        }

        $licenseData = FinancialDoctorApi::makeKey([
            'username'    => trim("$user->lastname $user->firstname $user->patronymic"),
            'birthday'    => $user->birth,
            'phone'       => $user->phone_mobile,
            'email'       => $user->email,
            'address'     => trim("$user->Regregion, $user->Regcity, $user->Regstreet, д. $user->Regbuilding"),
            'passport'    => $user->passport_serial,
            'contract'    => $contract->number,
            'order'       => $orderId,
            'tariff'      => $fullPaymentDone ? '20F' : null,
            'contractSum' => $fullPaymentDone ? null : $order->amount,
        ]);

        if (!$licenseData) {
            $this->jsonResponse(false, 'Ошибка при генерации ключа');
            return;
        }

        $this->credit_doctor->saveLicense([
            'user_id'        => $userId,
            'order_id'       => $orderId,
            'phone'          => $user->phone_mobile,
            'license_key'    => $licenseData['key'],
            'tariff'         => $licenseData['tariff'],
            'created_at'     => date('Y-m-d H:i:s', $licenseData['created'] / 1000),
            'ending'         => date('Y-m-d H:i:s', $licenseData['ending'] / 1000),
            'organization_id' => $order->organization_id,
        ]);

        if (!empty($fullPaymentDone)) {
            $this->users->updateGift($userId, ['got_gift' => true]);
            unset($_SESSION['full_payment_amount_done']);
        }

        $this->jsonResponse(true, 'Лицензия успешно сгенерирована', ['login_url' => sprintf(FinancialDoctorApi::LOGIN_URL, $licenseData['key'])]);
    }

    private function jsonResponse(bool $success, string $message, array $data = [])
    {
        echo json_encode(array_merge(['success' => $success, 'message' => $message], $data));
    }
}

(new GenerateFDKey())->processRequest();