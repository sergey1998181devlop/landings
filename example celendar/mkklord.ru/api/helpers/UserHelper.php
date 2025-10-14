<?php

namespace api\helpers;

/**
 * Class UserHelper
 */
class UserHelper
{
    /**
     * Флоу ФИО + паспорт вводяться До телефона
     */
    public const FLOW_AFTER_PERSONAL_DATA = 1;

    /**
     * Проверка пользователя на наличие просрочки и установки его для снятия допов
     * @param \Simpla $simpla
     * @param object $user
     * @return bool
     * @throws \Exception
     */
    public static function hasNotOverdueLoan(\Simpla $simpla, object $user): bool
    {
        // посчитаем просрочку у последнего закрытого займа
        $loans_closed = array_filter($user->loan_history, function ($loan) {
            return !empty($loan->close_date);
        });
        $end_loan = end($loans_closed);
        $plan_close_date = new \DateTime($end_loan->close_date);
        $interval_close_date = $plan_close_date->diff((new \DateTime($end_loan->plan_close_date)));
        $overdue_last_close_loan = $interval_close_date->days > 0 && $interval_close_date->invert === 1; // если просрочил
        $user_has_overdue_in_table = $simpla->users->hasOverdueHideUserService(
            $user->phone_mobile
        ); // есть пользователь в списке в таблице
        $notOverdueLoan = !$overdue_last_close_loan
            && $user_has_overdue_in_table
            && count($loans_closed) > 3
            && count($loans_closed) < 9; // больше 3 и менее 9 закрытых займов и по крайнему нет просрочки

        if ($user_has_overdue_in_table) {
            if ($notOverdueLoan) {
                $simpla->hide_service->addItem($user->id);
            } else {
                $simpla->hide_service->deleteItem($user->id);
            }
        }

        return $notOverdueLoan;
    }

    /**
     * Генерирует токен и записывает его в coockie
     * @param string $hmac_secret_key
     * @param int $user_id
     * @param string $token_key
     * @param int $expiration_time
     * @param bool $clear_old
     * @return mixed|string
     */
    public static function getJWTToken(string $hmac_secret_key, int $user_id, string $token_key, int $expiration_time = 3600, bool $clear_old = false)
    {
        if ($clear_old) {
            setcookie($token_key, null, time()-1, '/');
            $_COOKIE[$token_key] = null;
        }

        if (!empty($_COOKIE[$token_key])) {
            return $_COOKIE[$token_key];
        } else {
            $token = \api\helpers\JWTHelper::generateToken($hmac_secret_key, $user_id, $expiration_time);
            setcookie($token_key, $token, time() + $expiration_time, '/');
            return $token;
        }
    }

    /**
     * @return mixed|null
     */
    public static function getFlow()
    {
        return $_SESSION['user_flow'] ?? null;
    }

    /**
     * Извлекает серию и номер паспорта
     * @param string $passport_serial
     * @return array
     */
    public static function unserializePassport(string $passport_serial): array
    {
        preg_match( '@(\d{2}\s\d{2})\s(\d{6})@', $passport_serial, $passport);
        return [$passport[1], $passport[2]];
    }
}
