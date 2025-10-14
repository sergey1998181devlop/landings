<?php

require_once 'View.php';

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class ComplaintView extends View
{
    private const TELEGRAM_BOT_TOKEN = '7555812531:AAFH-BjYIJIkgwxuDyU2ZFOeqzm43SB22Uc';

    private const TELEGRAM_CHAT_ID = '-1002459695515';

    private const MESSAGE_THREAD_ID = '924';
    //private const MESSAGE_THREAD_ID = '396'; # TEST

    private const PAUSE_MINUTES = 10;

    private const MAX_FILES_COUNT = 5;

    private const MAX_FILE_SIZE = 20000000;

    private const EMAIL = 'info@boostra.ru';
    //private const EMAIL = 'igorvoskoboynick@gmail.com';

    private static $available_image_exts = [
        "image/png",
        "image/jpeg",
    ];

    private static $available_doc_exts = [
        "application/pdf",
        "application/msword",
        "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
    ];

    public $complaintTopics = [
        ['id' => 1, 'yandex_goal_id' => 'complaint_reason_goal1', 'name' => 'получение займа'],
        ['id' => 2, 'yandex_goal_id' => 'complaint_reason_goal2', 'name' => 'начисления по займу'],
        ['id' => 3, 'yandex_goal_id' => 'complaint_reason_goal3', 'name' => 'дополнительные услуги'],
        ['id' => 4, 'yandex_goal_id' => 'complaint_reason_goal4', 'name' => 'процедура взыскания'],
        ['id' => 5, 'yandex_goal_id' => 'complaint_reason_goal5', 'name' => 'оспаривание КИ / мошенничество'],
        ['id' => 6, 'yandex_goal_id' => 'complaint_reason_goal6', 'name' => 'иное'],
    ];

    public function fetch()
    {
        if ($this->request->method('post')) {
            $this->createComplaint();
        }

        $this->design->assign('user', $this->user);
        $this->design->assign('complaint_topics', $this->complaintTopics);
        $this->design->assign('eighteen_years_birthdate', date('Y-m-d', strtotime('-18 years')));

        return $this->design->fetch('complaint/index.tpl');
    }

    private function createComplaint(): void
    {
        $name = $this->request->post('complaint_name');
        $phone = $this->request->post('complaint_phone');
        $email = $this->request->post('complaint_email');
        $birth = $this->request->post('complaint_birth');
        $topic = $this->complaintTopics[0]['name'];

        if (!empty($this->request->post('complaint_topic'))) {
            $topic = $this->complaintTopics[$this->request->post('complaint_topic') - 1]['name'];
        }

        $text = $this->request->post('complaint_text');
        $files = $this->request->files('complaint_file');

        if (empty($name) || empty($phone) || empty($email) || empty($birth) || empty($topic) || empty($text)) {
            header("Content-type: application/json; charset=UTF-8");
            echo json_encode(['error' => 'empty_required_fields']);
            exit;
        }

        $exist_complaint = $this->complaint->get_limit($name, $phone, $email, $birth);
        if ($exist_complaint && !empty($exist_complaint->created)) {
            $current_date = new DateTime(date('Y-m-d H:i:s'));
            $created = new DateTime($exist_complaint->created);
            $diff = $current_date->diff($created);

            if ($diff->h == 0 && $diff->i <= static::PAUSE_MINUTES) {
                header("Content-type: application/json; charset=UTF-8");
                echo json_encode(['error' => 'time_limit']);
                exit;
            }
        }

        if ($this->user) {
            $this->userEmails->syncEmail($this->user, $email, UserEmails::SOURCE_COMPLAINT_EMAIL);
        }

        try {
            $media = [];
            $media_files = [];
            $complaint_files = [];
            if (!empty($files)) {
                if (count($files['name']) > static::MAX_FILES_COUNT) {
                    header("Content-type: application/json; charset=UTF-8");
                    echo json_encode(['error' => 'max_files']);
                    exit;
                }

                foreach ($files['name'] as $num => $file) {
                    if ($files['size'][$num] >= static::MAX_FILE_SIZE) {
                        header("Content-type: application/json; charset=UTF-8");
                        echo json_encode(['error' => 'max_file_size']);
                        exit;
                    }

                    if (!in_array($files['type'][$num], static::$available_image_exts) && !in_array($files['type'][$num], static::$available_doc_exts)) {
                        header("Content-type: application/json; charset=UTF-8");
                        echo json_encode(['error' => 'error_file_type']);
                        exit;
                    }

                    if (!is_dir($this->config->root_dir . 'files/complaints/')) {
                        mkdir($this->config->root_dir . 'files/complaints/');
                    }

                    $new_filename = $this->config->root_dir . 'files/complaints/' . md5(microtime() . mt_rand()) . $file;
                    $file_uploaded = move_uploaded_file($files['tmp_name'][$num], $new_filename);
                    if ($file_uploaded) {
                        $complaint_files[] = $new_filename;
                        $media[] = [
                            'type' => 'document',
                            'media' => 'attach://' . $file,
                        ];

                        $media_files[$file] = curl_file_create($new_filename, $files['type'][$num], $files['name'][$num]);
                    }
                }
            }

            $this->complaint->add_complaint(
                [
                    'fio' => $name,
                    'phone' => $phone,
                    'email' => $email,
                    'birth' => $birth,
                    'topic' => $topic,
                    'message' => $text,
                    'files' => json_encode($complaint_files)
                ]
            );

            $telegram = new TelegramApi([
                'token' => self::TELEGRAM_BOT_TOKEN,
                'chat_id' => self::TELEGRAM_CHAT_ID,
                'message_thread_id' => self::MESSAGE_THREAD_ID
            ]);

            $tgMessage = $this->setMessage($name, $phone, $email, $birth, $topic, $text);
            $telegram->sendMessage($tgMessage);
            if ($media && $media_files) {
                $telegram->sendMediaGroup($media, $media_files);
            }

            $emailMessage = $this->setMessage($name, $phone, $email, $birth, $topic, $text, true);
            $this->sendEmail($emailMessage, $complaint_files);

            header("Content-type: application/json; charset=UTF-8");
            echo json_encode(['message' => 'Обращение отправлено.']);
            exit;
        } catch (\Exception $e) {
            $this->logging('ERROR', 'Error send complaint', '', ['error' => $e->getMessage()], 'complaint.txt');

            header("Content-type: application/json; charset=UTF-8");
            echo json_encode(['message' => 'Сейчас невозможно отправить обращение.']);
            exit;
        }
    }

    private function sendEmail($message, array $uploaded_files): void
    {
        try {
            $phpMailer = new PHPMailer();
            $phpMailer->SMTPDebug = 0;
            $phpMailer->isSMTP();
            $phpMailer->Host = 'smtp.mail.ru';
            $phpMailer->SMTPAuth = true;
            $phpMailer->Username = $this->config->mail_mp_user_name;
            $phpMailer->Password = $this->config->mail_mp_password;
            $phpMailer->SMTPSecure = 'ssl';
            $phpMailer->Port = 465;

            $phpMailer->addAddress(static::EMAIL);
            $phpMailer->CharSet = 'UTF-8';

            $phpMailer->setFrom($this->config->mail_mp_user_name, 'Boostra Mail Agent');
            $phpMailer->Subject = "Boostra. Клиент отправил форму жалобы";
            $phpMailer->isHTML(true);
            $phpMailer->Body = $message;

            if ($uploaded_files) {
                foreach ($uploaded_files as $file) {
                    $phpMailer->addAttachment($file, basename($file));
                }
            }

            $phpMailer->send();
        } catch (Exception $e) {
            return;
        }
    }

    private function setMessage($name, $phone, $email, $birth, $topic, $text, $isHTML = false): string
    {
        $eol = PHP_EOL;
        $quoteStart = '<blockquote>';
        $quoteEnd = '</blockquote>';
        if ($isHTML) {
            $eol = '<br>';
        }

        return sprintf(
            "Клиент отправил форму жалобы" . $eol . $eol .
            "<b>Клиент</b>: %s" . $eol .
            "<b>Телефон</b>: %s" . $eol .
            "<b>Email</b>: %s" . $eol .
            "<b>Дата рождения</b>: %s" . $eol . $eol .
            "<b>Тема обращения</b>: %s" . $eol . $eol .
            $quoteStart . "<b>%s</b>" . $quoteEnd,
            $name,
            $phone,
            $email,
            $birth,
            $topic,
            $text
        );
    }
}
