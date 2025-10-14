<?php

namespace ajax;
require_once(dirname(__DIR__) . '/api/Simpla.php');

use api\services\FileStorageService;
use Exception;
use Simpla;

class UploadPaymentRsHandler extends Simpla
{
    private FileStorageService $fileStorageService;

    public function __construct()
    {
        parent::__construct();

        $this->fileStorageService = new FileStorageService(
            $this->config->PAYMENTS_RS_STORAGE['endpoint'],
            $this->config->PAYMENTS_RS_STORAGE['region'],
            $this->config->PAYMENTS_RS_STORAGE['key'],
            $this->config->PAYMENTS_RS_STORAGE['secret'],
            $this->config->PAYMENTS_RS_STORAGE['bucket']
        );

        $this->handle();
    }

    private function handle(): void
    {
        $orderId = $this->request->post('order_id', 'integer');
        $userId = $this->request->post('user_id', 'integer');
        $contractNumber = $this->request->post('contract_number', 'string');

        if (!$orderId || empty($_FILES['rs_file'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing order_id or file.']);
            exit;
        }

        $allowedTypes = ['image/png', 'image/jpeg', 'image/heif', 'image/heic', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        if (!in_array($_FILES['rs_file']['type'], $allowedTypes)) {
            http_response_code(400);
            echo json_encode(['error' => 'Неподдерживаемый формат файла.']);
            exit;
        }

        $maxFileSize = 100 * 1024 * 1024; // 100 МБ в байтах
        if ($_FILES['rs_file']['size'] > $maxFileSize) {
            http_response_code(400);
            echo json_encode(['error' => 'Размер файла превышает 100 МБ.']);
            exit;
        }

        $file = $_FILES['rs_file'];
        $filePath = $file['tmp_name'];
        $fileName = basename($file['name']);
        $fileKey = "payments_rs/$orderId/" . uniqid() . '_' . $fileName;

        $this->db->query("
            SELECT id FROM __contracts WHERE order_id = ? AND number = ?",
            $orderId, $contractNumber
        );
        $contractId = $this->db->result('id');

        if (!$contractId) {
            http_response_code(400);
            echo json_encode(['error' => 'Договор не найден.']);
            exit;
        }

        // Пользователь может отправить данные по одному договору только один раз в течение текущего дня.
        $todayStart = date('Y-m-d 00:00:00');
        $todayEnd = date('Y-m-d 23:59:59');

        $this->db->query("
                SELECT id FROM __payments_rs 
                WHERE contract_id = ? 
                  AND created_at BETWEEN ? AND ?
                LIMIT 1
                ", $contractId, $todayStart, $todayEnd);

        if ($this->db->result('id')) {
            http_response_code(400);
            echo json_encode([
                'error' => 'Вы уже загружали файл по этому договору сегодня. Повторная загрузка невозможна.'
            ]);
            exit;
        }

        try {
            $this->fileStorageService->putFile($filePath, $fileKey);

            $this->db->query("
                INSERT INTO __payments_rs (user_id, order_id, contract_id, name, attachment)
                VALUES (?, ?, ?, ?, ?)",
                $userId, $orderId, $contractId, $fileName, $fileKey
            );

            echo json_encode(['success' => true, 'file_key' => $fileKey]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Ошибка при сохранении файла: ' . $e->getMessage()]);
        }

        exit;
    }
}

new UploadPaymentRsHandler();
