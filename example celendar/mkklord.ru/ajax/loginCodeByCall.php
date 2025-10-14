<?php

$__ref = $_SERVER['HTTP_REFERER'] ?? '';
$__ref_path = $__ref ? parse_url($__ref, PHP_URL_PATH) : '';

header("Content-type: application/json; charset=UTF-8");
header("Cache-Control: must-revalidate");
header("Pragma: no-cache");
header("Expires: -1");

if ($__ref_path && strpos($__ref_path, '/user/login') === 0) {
    echo json_encode([
        'response' => 'exist_user',
        'asp_additional_doc' => true,
    ]);
    exit;
}

echo json_encode([
    'success' => true,
]);
exit;

error_reporting(0);
ini_set('display_errors', 'Off');
session_start();

require_once('../api/Simpla.php');
require_once('../api/Helpers.php');

$simpla = new Simpla();

$apikeys = $simpla->settings->apikeys;

define("SMS_LOGIN", $apikeys['smsc']['login']);
define("SMS_PASSWORD", $apikeys['smsc']['password']);

$phone = $simpla->request->post('phone');

if (!session_id())
    @ session_start();

$_SESSION['check_sms_count'] = 0;
$result = array();
$sms_delay = 30;

if (!$simpla->is_developer && $_SESSION['sms_count'] > 0) {
    $smart_token = $simpla->request->post('smart-token') ?: '';
    $result['captcha_error'] = !\api\YaSmartCaptcha::check_captcha($smart_token);
    $_SESSION['init_captcha_login'] = true;
}

$existUser = $simpla->users->get_user($phone);

$isBlocked = Helpers::isBlockedUserBy1C($simpla, $phone);
$isBeeOperator = getOperatorInfo($phone);
$resultResponse = [];
$asp_additional_doc = $existUser && getUserDue($existUser, $simpla);
if ($simpla->request->post('check_user') && empty($result['captcha_error'])) {

    $huid = $simpla->request->post('huid');
    if ($huid != $simpla->settings->hui) {
        $result['error'] = 'Обновите страницу и попробуйте еще раз';
        $result['error_type'] = 'exception';
        header("Content-type: application/json; charset=UTF-8");
        echo json_encode($result);
        exit;
    }

    if ($existUser && !$isBeeOperator) {
        if ($isBlocked) {
            $simpla->request->json_output(['error' => 'Ваш кабинет недоступен. Для решения проблемы, пожалуйста, позвоните по номеру <a style="color: red;font-weight: bold" href="tel:88003333073">8-800-333-3073</a>']);
            exit;
        } else {
            $resultResponse = ['response' => 'exist_user', 'asp_additional_doc' => $asp_additional_doc];
            header("Content-type: application/json; charset=UTF-8");
            echo json_encode($resultResponse);
            exit;
        }
    } elseif ($existUser && $isBeeOperator) {
        $resultResponse = ['response' => 'user_bee','asp_additional_doc' => $asp_additional_doc];
        header("Content-type: application/json; charset=UTF-8");
        echo json_encode($resultResponse);
        exit;
    } else {
        if (empty($_SERVER['HTTP_REFERER'])) {
            $result['error'] = 'Обновите страницу и попробуйте еще раз';
            $result['error_type'] = 'exception';
            header("Content-type: application/json; charset=UTF-8");
            echo json_encode($result);
            exit;

        } else {
            $soap = $simpla->soap->get_uid_by_phone($phone);
            if (empty($soap->uid)) {
                $_SESSION['user_modal_phone'] = $phone; // записывает номер телефона для дальнейшего шага, чтобы скрыть при регистрации
                $result['error'] = 'Пользователь не найден. Подайте заявку, используя калькулятор на главной странице';

                if ($simpla->request->post('page_action') === 'send_complaint') {
                    $email = $simpla->config->org_email;
                    $result['error'] = 'Номер телефона не найден. Если Вы не являетесь клиентом компании направьте Ваше обращение на <a href="mailto:'.$email.'" style="color: red; font-weight: bold">'.$email.'</a>';
                }

                $result['error_type'] = 'user_not_find';
                header("Content-type: application/json; charset=UTF-8");
                echo json_encode($result);
                exit;
            } else {
                $simpla->request->json_output(['response' => 'exist_user','asp_additional_doc' => $asp_additional_doc]);
            }

        }
    }
}

if (!empty($_SESSION['sms_time']) && ($_SESSION['sms_time'] + $sms_delay) > time()) {
    $result['error'] = 'sms_time';
    $result['time_left'] = $_SESSION['sms_time'] + $sms_delay - time();
}

if (isset($_SESSION['sms_count']) && (empty($simpla->is_developer) && empty($simpla->is_admin)))
    $_SESSION['sms_count']++;
else
    $_SESSION['sms_count'] = 1;

$_SESSION['sms_date'] = strtotime('now');

$_SESSION['phone'] = $simpla->users->clear_phone($phone);

if (empty($result['captcha_error']) && $_SESSION['sms_count'] < 30 && empty($result['error'])) {
    if (empty($simpla->is_developer) && empty($simpla->is_admin)) {

        if ($simpla->request->post('flag') === 'LOGIN') {
            $sms_validate = Helpers::validateFloodSMS($simpla, 10, $_SESSION['phone']);
        } else {
            $sms_validate = null;
        }
        $_SESSION['sms_time'] = time();

        $url = 'https://smsc.ru/sys/send.php?login=' . SMS_LOGIN . '&psw=' . SMS_PASSWORD . '&phones=' . $phone . '&mes=code&call=1';
        $resultString = file_get_contents($url);
        preg_match('/.+ (CODE - (?<code>\d+))/ui', $resultString, $match);
        if (isset($match['code'])) {
            $code = substr($match['code'], -4);
            $_SESSION['sms'] = $code;
            $simpla->authcodes->add_authcode(array(
                'phone' => $phone,
                'code' => $code,
                'created' => date('Y-m-d H:i:s')
            ));
        }

        $insert_data = [
            'phone' => $_SESSION['phone'],
            'ip' => $_SERVER['REMOTE_ADDR'],
            'sms_time' => $_SESSION['sms_time'],
            'total' => ($sms_validate->total ?? 0) + 1
        ];

        $simpla->sms_validate->updateFloodSMS($sms_validate, $insert_data);

        /*else {
            $code = rand(1000, 9999);
            $_SESSION['sms'] = $code;

            $msg = iconv('utf8', 'cp1251', 'Ваш код для регистрации на boostra.ru: '.$_SESSION['sms']);
            $response = $simpla->notify->send_sms($_SESSION['phone'], $msg, 'Boostra.ru', 1);

            $simpla->sms->add_message(
                [
                    'phone' => $phone,
                    'message' => 'Ваш код для регистрации на boostra.ru: ' . $_SESSION['sms'],
                    'send_id' => $response,
                    'created' => date('Y-m-d H:i:s', time()),
                ]
            );

            $simpla->authcodes->add_authcode(
                [
                    'phone' => $phone,
                    'code' => $code,
                    'created' => date('Y-m-d H:i:s')
                ]
            );
        }*/

    } else {
        $code = rand(1000, 9999);
        $_SESSION['sms'] = $code;
        $simpla->authcodes->add_authcode(array(
            'phone' => $phone,
            'code' => $code,
            'created' => date('Y-m-d H:i:s')
        ));
        $result['developer_code'] = $code;
    }
}

function getOperatorInfo($phone): bool
{
    $login = SMS_LOGIN;
    $password = SMS_PASSWORD;
    $url = "https://smsc.ru/sys/info.php?get_operator=1&login=$login&psw=$password&phone=$phone";
    $resultString = file_get_contents($url);

    if ($resultString === false) {
        return false;
    }

    $result = iconv("CP1251", "UTF-8", $resultString);

    return strpos($result, 'Билайн') !== false;
}

function getUserDue($user, \Simpla $simpla): bool
{
    $response_balances = $simpla->soap->get_user_balances_array_1c($user->uid);
    foreach ($response_balances as $response) {
        $utc_payment_date = strtotime($response['ПланДата']);
        $utc_now = strtotime(date('Y-m-d 00:00:00'));
        if ($utc_now > $utc_payment_date &&  !($simpla->users->getZaimAspStatus($response['НомерЗайма']))) {
            return true;
        }
    }
    return false;
}
session_write_close();

header("Content-type: application/json; charset=UTF-8");
header("Cache-Control: must-revalidate");
header("Pragma: no-cache");
header("Expires: -1");
print json_encode($result);