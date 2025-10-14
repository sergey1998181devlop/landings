<?php

require_once './ajaxController.php';
require_once '../api/Scorings.php';

/**
 * AJAX файл вызывается перед привязкой карты и на основании уже запущенных скорингов принимает решение о том,
 * нужно ли продать карту клиента партнёрам.
 *
 * Критерий продажи - НК клиент **И**:
 * - Клиент найден в чс
 * - Отказ по возрасту
 * - Отказ по региону
 * - Банкрот (ЕФРСБ скоринг)
 * - Отказ по скористе и акси одновременно
 *
 * Клиенты, которых мы решаем продать, получают ссылку на привязку карты не на нашем сайте.
 *
 * Если клиенту всё одобрено - пропускаем его на привязку карты на нашем сайте.
 *
 * Если один из скорингов выдал ошибку - пропускаем клиента на привязку карты на нашем сайте.
 *
 * Если клиент сидит на экране ожидания решения более 1.5 минуты - пропускаем клиента на привязку карты на нашем сайте.
 * (Например, если какой-то из скорингов долго тормозит, но не упал в ошибку).
 *
 * Признак проданности клиента партнёру - ключ `is_rejected_nk` в `user_data`:
 * - `null` - Решение ещё не принималось (В том числе для старых клиентов).
 * - `0` - Клиент хороший, с ним работаем.
 * - `1` - Клиент может быть продан партнёрам, с ним не работаем.
 *
 * ```
 * $is_good_guy = $this->user_data->read($user_id, 'is_rejected_nk') == 0;
 * ```
 *
 * Часть потока проходит мимо флоу с продажей.
 * Решение о том, прошёл ли клиент мимо флоу записывается в `rejected_nk_skipped` в `user_data`:
 * - `0` - Клиент проходил флоу (Но необязательно продан).
 * - `1` - Клиент пропустил флоу.
 * ```
 * $is_flow_skipped = $this->user_data->read($user_id, 'rejected_nk_skipped') == 1;
 * ```
 *
 * В данный момент по флоу не проходит органика с 10 до 17 МСК, остальные
 * клиенты (и органика вне этого промежутка) проходят по флоу только если  подходят
 * под настройки https://manager.boostra.ru/bonon_settings
 *
 * @see CheckScoringsNk::actionCheck()
 */
class CheckScoringsNk extends ajaxController
{
    /**
     * Список скорингов которые нужно дождаться для вынесения решения.
     */
    const REQUIRED_SCORINGS = [
        // Запускаются после заполнения ФИО
        Scorings::TYPE_AGE,
        Scorings::TYPE_BLACKLIST,
        // После заполнения паспортных данных
        Scorings::TYPE_FNS,
        Scorings::TYPE_EFRSB,
        // После заполнения прописки
        Scorings::TYPE_LOCATION,
        // Перед привязкой карты (Экран ожидания решения)
        Scorings::TYPE_AXILINK_2,
        Scorings::TYPE_SCORISTA,
    ];

    /**
     * Причины отказов
     */
    const REJECT_REASONS = [
        // Для скоринга АксиНБКИ отдельная логика получения причины отказа, Ctrl+F getAxiRejectReason
        Scorings::TYPE_AGE => 23,
        Scorings::TYPE_BLACKLIST => 2,
        // Scorings::TYPE_FNS - Не может дать отказ
        Scorings::TYPE_EFRSB => 22,
        Scorings::TYPE_LOCATION => 14,
        Scorings::TYPE_SCORISTA => 5,
        Scorings::TYPE_WORK => 40,
    ];

    /** @var int Настройка для пропуска отказного флоу срабатывает всегда */
    const SKIP_CHANCE_0 = 0;

    /** @var int Настройка для пропуска отказного флоу делит поток пополам */
    const SKIP_CHANCE_50 = 1;

    /** @var int Настройка для пропуска отказного флоу на паузе */
    const SKIP_CHANCE_100 = 2;

    private const LOG_FILE = 'check_scorings_nk.txt';

    /**
     * Список действий которые можно вызывать с фронта
     * @return array[]
     */
    public function actions(): array
    {
        return [
            'check' => [
                'timeout' => 'string',
            ],
            'partnerClicked' => [true],
        ];
    }

    /**
     * Клиенту нужно подождать скоринги.
     * @return array
     */
    function returnIsNotReady(): array
    {
        return ['ready' => false];
    }

    /**
     * Пропускаем клиента на привязку настоящей карты.
     *
     * С клиентом всё хорошо, либо при проверках произошла ошибка.
     * @return array
     */
    function returnApprove(): array
    {
        $this->user_data->set($this->user->id, 'is_rejected_nk', 0);

        if ($last_order = $this->orders->get_last_order($this->user->id)) {
            if ($last_order->status == $this->orders::STATUS_REJECTED) {
                $this->leadgid->reject_actions($last_order->id);
            }
        }

        return ['ready' => true];
    }

    /**
     * Продаём клиента партнёру и пропускаем на привязку парнёрской карты.
     *
     * Клиент провалил один из важных скорингов.
     * @param int $reason_id Причина отказа
     * @return array
     */
    function returnDecline(int $reason_id): array
    {
        $this->user_data->set($this->user->id, 'is_rejected_nk', 1);

        // Отказ по заявке без отправки постбеков и смс
        if ($order = $this->orders->get_last_order($this->user->id)) {
            $this->order_data->set($order->id, 'is_sold_to_bonon', 1);

            if ($order->status != $this->orders::STATUS_REJECTED) {
                $manager = $this->managers->get_crm_manager($this->managers::MANAGER_SYSTEM_ID);

                $update_order = [
                    'status' => $this->orders::STATUS_REJECTED,
                    'manager_id' => $manager->id,
                    'reason_id' => $reason_id,
                    'reject_date' => date('Y-m-d H:i:s')
                ];
                $this->orders->update_order($order->id, $update_order);

                $changeLogs = Helpers::getChangeLogs($update_order, $order);
                $this->changelogs->add_changelog(array(
                    'manager_id' => $manager->id,
                    'created' => date('Y-m-d H:i:s'),
                    'type' => 'status',
                    'old_values' => serialize($changeLogs['old']),
                    'new_values' => serialize($changeLogs['new']),
                    'order_id' => $order->id,
                    'user_id' => $order->user_id,
                ));

                $reason = $this->reasons->get_reason($reason_id);
                $this->soap->update_status_1c($order->{'1c_id'}, $this->orders::ORDER_1C_STATUS_REJECTED_FOR_SEND, $manager->name_1c, 0, 1, $reason->admin_name);
                $this->soap->send_order_manager($order->{'1c_id'}, $manager->name_1c);

                $this->scorings->stopOrderScorings($order->id, ['string_result' => 'Причина: Карта отказного НК продана']);
            }
        }

        if ($this->short_flow->isShortFlowUser($this->user->id)) {
            $this->short_flow->setRegisterStage($this->user->id, $this->short_flow::STAGE_BONON);
        }

        return ['ready' => true, 'decision' => 'decline'];
    }

    /**
     * Поиск подходящей настройки по продаваемости источника
     *
     * https://manager.boostra.ru/bonon_settings
     * @param string $utm_source
     * @param string $utm_medium
     * @return ArrayObject|false
     */
    function getBononSourceSetting($utm_source, $utm_medium)
    {
        $sources = $this->settings->bonon_sources;
        if (empty($sources))
            return false;

        // Ищем наиболее подходящую настройку, если есть
        $foundSource = $foundAny = false;
        foreach ($sources['rows'] as $source) {
            if ($source->utm_source == $utm_source) {
                if ($source->utm_medium == $utm_medium)
                    return $source; // Точное совпадение по utm_source и utm_medium
                elseif ($source->utm_medium == '*')
                    $foundSource = $source; // Совпадение по utm_source, общая настройка для всех utm_medium
            }
            elseif ($source->utm_source == '*')
                $foundAny = $source; // Общая настройка для всех utm_source
        }
        return $foundSource ?: $foundAny;
    }

    /**
     * Проверяем, должен ли клиент проходить по флоу с возможной продажей карты.
     *
     * По флоу не проходит органика с 10 до 17 МСК.
     *
     * В остальных случаях клиент проходит по флоу если подходит под настройки https://manager.boostra.ru/bonon_settings
     * @return bool
     */
    function trySkipCheck(): bool
    {
        $is_skipped = $this->user_data->read($this->user->id, 'rejected_nk_skipped');
        if (isset($is_skipped))
            return $is_skipped == 1; // Проверка уже проводилась

        foreach ([$this->user->lastname, $this->user->firstname, $this->user->patronymic] as $str) {
            if (mb_strtolower($str) == 'тест') {
                // Это тестовый аккаунт
                $this->user_data->set($this->user->id, 'rejected_nk_skipped', 1);
                return true;
            }
        }

        $inn_not_found = $this->user_data->read($this->user->id, 'inn_not_found');
        if (!empty($inn_not_found)) {
            // Данные заполнены некорректно, ИНН не нашло
            $this->user_data->set($this->user->id, 'rejected_nk_skipped', 1);
            return true;
        }

        // Проверки для органики
        if ($this->users->checkUtmSource($this->user->id)) {
            // Клиент - органика
            $dayOfWeek = date('N');
            if ($dayOfWeek < 6) {
                // Рабочий день
                $currentHour = date('G');
                if ($currentHour >= 10 && $currentHour <= 17) {
                    // Промежуток между 10 и 17 МСК, в это время действует безопасный флоу
                    // Органика пропускает этап с проверкой на необходимость продажи
                    $this->user_data->set($this->user->id, 'rejected_nk_skipped', 1);
                    return true;
                }
            }
        }

        // Пропуск повторных НК
        if ($orders = $this->orders->get_orders(['user_id' => $this->user->id])) {
            if (count($orders) > 1) {
                // У клиента >1 заявки, пропускаем повторного НК мимо продажи
                $this->user_data->set($this->user->id, 'rejected_nk_skipped', 1);
                return true;
            }
        }

        // Ищем подходящую настройку
        $setting = $this->getBononSourceSetting($this->user->utm_source, $this->user->utm_medium);
        if (empty($setting)) {
            // utm_source в заявке может отличаться, ищем настройку по ней тоже
            $last_order = $this->orders->get_last_order($this->user->id);
            if (!empty($last_order)) {
                $setting = $this->getBononSourceSetting($last_order->utm_source, $last_order->utm_medium);
            }
        }

        if (empty($setting)) {
            // Настройки на продажу нет, пропускаем
            $this->user_data->set($this->user->id, 'rejected_nk_skipped', 1);
            return true;
        }

        switch ($setting->chance) {
            case self::SKIP_CHANCE_0:
                // Всегда идёт по флоу
                $this->user_data->set($this->user->id, 'rejected_nk_skipped', 0);
                return false;

            case self::SKIP_CHANCE_50:
                // 50% шанс пропуска, разделение потока
                $is_skipped = rand(0, 1);
                $this->user_data->set($this->user->id, 'rejected_nk_skipped', $is_skipped);
                return $is_skipped == 1;

            case self::SKIP_CHANCE_100:
            default:
                // На паузе
                $this->user_data->set($this->user->id, 'rejected_nk_skipped', 1);
                return true;
        }
    }

    public function actionCheck(): array
    {
        if ($this->trySkipCheck()) {
            // Клиент должен идти по обычному флоу без вероятности продажи карты
            return $this->returnApprove();
        }

        $is_timeout = $this->data['timeout'] == 'true';
        if ($is_timeout) {
            // Клиент ждёт скоринги слишком долго
            // Пропускаем на этап привязки карты
            $this->user_data->set($this->user->id, 'rejected_nk_timeout', 1);
            return $this->returnApprove();
        }

        if ($last_order = $this->orders->get_last_order($this->user->id)) {
            if ($last_order->status == $this->orders::STATUS_REJECTED) {
                // Заявка уже в отказе, продаём клиента
                return $this->returnDecline($last_order->reason_id);
            }
        }

        $scorings = $this->scorings->get_scorings([
            'user_id' => $this->user->id,
            'type' => self::REQUIRED_SCORINGS
        ]);

        if (empty($scorings)) {
            // Так быть не должно, но такое, в теории, может произойти.
            // В таком случае пропускаем клиента дальше, на этап привязки карты
            return $this->returnApprove();
        }

        $has_uncompleted_scoring = false;
        $axi_scoring = null;
        $scoring_result = array_fill_keys(self::REQUIRED_SCORINGS, -1);
        foreach ($scorings as $scoring) {
//            if ($scoring->status == Scorings::STATUS_ERROR) {
//                // Ошибка в одном из скорингов, пропускаем клиента на привязку карты
//                return $this->returnApprove();
//            }
            if ($scoring->status == Scorings::STATUS_ERROR) {
                // У скорингов с ошибкой не смотрим на результат и пропускаем их
                continue;
            }

            if ($scoring->status != Scorings::STATUS_COMPLETED) {
                $has_uncompleted_scoring = true;
                continue;
            }

            if ($scoring->type == Scorings::TYPE_AXILINK_2) {
                $axi_scoring = $scoring;
            }

            $scoring_result[$scoring->type] = $scoring->success;
        }

        // Если все скоринги выполнились, но скориста и акси ещё не добавлены - нужно их подождать
        // Проверка на ещё не добавленные скоринги
        if (!$has_uncompleted_scoring) {
            foreach (self::REQUIRED_SCORINGS as $required_type) {
                $found = false;
                foreach ($scorings as $scoring) {
                    if ($scoring->type == $required_type) {
                        $found = true;
                        break;
                    }
                }

                if (!$found) {
                    $has_uncompleted_scoring = true;
                    break;
                }
            }
        }

        // Смотрим, есть ли отказ по одному из скорингов и, если есть - продаём клиента
        foreach ($scoring_result as $type => $result) {
            if ($result == 0) {
                if ($type == $this->scorings::TYPE_AXILINK_2) {
                    $axi_body = $this->scorings->get_body_by_type($axi_scoring);
                    if (!empty($axi_body['message'])) {
                        $axi_reason = $this->scorings->getAxiRejectReason($this->user->id, $axi_body['message']);
                        if (!empty($axi_reason)) {
                            return $this->returnDecline($axi_reason);
                        }
                    }
                }

                if ($type == $this->scorings::TYPE_SCORISTA &&
                    $last_order = $this->orders->get_last_order($this->user->id)) {
                    if (!$this->scorings->isScoristaAllowed($last_order)) {
                        continue;
                    }
                }

                return $this->returnDecline(self::REJECT_REASONS[$type] ?? $this->reasons::REASON_CARD_SELLED_TO_BONON);
            }
        }

        // Никакие скоринги не отказали
        if ($has_uncompleted_scoring) {
            // Но не все завершились
            return $this->returnIsNotReady();
        }

        $needNotifyUserWhenScoristaSuccess = $this->settings->need_notify_user_when_scorista_success;

        // Если включен функционал отправки смс при одобрении скористы и скориста одобрила, то отправляем смс
        if (!empty($needNotifyUserWhenScoristaSuccess)) {
            foreach ($scorings as $scoring) {
                if ((int)$scoring->type === $this->scorings::TYPE_SCORISTA && !empty($scoring->success)) {
                    $this->sendSmsSuccessScorista($scoring);
                    break;
                }
            }
        }

        return $this->returnApprove();
    }

    /**
     * Отправка смс об одобрении скористы
     *
     * @param stdClass $scoring
     * @return void
     */
    private function sendSmsSuccessScorista(stdClass $scoring): void
    {
        $sentSms = $this->sms->get_sent_sms($this->user->phone_mobile, date('Y-m-d H:i:s', strtotime('-1 day')));

        // Проверяем, что ранее не отправляли смс об одобрении скористы
        if (!empty($sentSms)) {
            foreach ($sentSms as $sms) {
                if ($sms->type === $this->sms::TYPE_SUCCESS_SCORISTA) {
                    $this->logging(__METHOD__, '', ['user_id' => $this->user->id, 'order_id' => $scoring->order_id, 'sms_id' => $sms->id], 'Смс об одобрении скористы уже был ранее отправлен пользователю', self::LOG_FILE);
                    return;
                }
            }
        }

        $smsTemplate = $this->sms->get_template($this->sms::SMS_TEMPLATE_SUCCESS_SCORISTA);

        if (empty($smsTemplate)) {
            $this->logging(__METHOD__, '', ['user_id' => $this->user->id, 'order_id' => $scoring->order_id], 'Шаблон смс не найден', self::LOG_FILE);
            return;
        }

        $text_message = $smsTemplate->template;

        if (strpos($text_message, '{{firstname}}') !== false) {
            $text_message = strtr($text_message, [
                '{{firstname}}' => $this->user->firstname,
            ]);
        }

        if (strpos($text_message, '{{amount}}') !== false) {
            $amountForSms = $this->user->first_loan_amount;

            if (!empty((int)$scoring->order_id)) {
                $order = $this->orders->get_order((int)$scoring->order_id);

                if (!empty($order) && !empty($order->amount)) {
                    $amountForSms = $order->amount;
                }
            }

            $text_message = strtr($text_message, [
                '{{amount}}' => number_format($amountForSms, 0, ',',' '),
            ]);
        }

        $text = iconv('UTF-8', 'cp1251', $text_message);
        $result = $this->notify->send_sms($this->user->phone_mobile, $text);
        $this->sms->add_message([
            'user_id' => $this->user->id,
            'order_id' => $scoring->order_id,
            'phone' => $this->user->phone_mobile,
            'message' => $text_message,
            'created' => date('Y-m-d H:i:s'),
            'send_status' => $result[1],
            'delivery_status' => '',
            'send_id' => $result[0],
            'type' => $this->sms::TYPE_SUCCESS_SCORISTA,
        ]);
    }

    public function actionPartnerClicked(): array
    {
        $this->user_data->set($this->user->id, 'rejected_nk_visited', 1);
        $this->users->update_user($this->user->id, [
            'card_added' => 1,
            'files_added' => 1,
            'additional_data_added' => 1
        ]);

        if ($this->short_flow->isShortFlowUser($this->user->id)) {
            $this->short_flow->setRegisterStage($this->user->id, $this->short_flow::STAGE_FINAL);
        }

        return ['refresh' => true];
    }
}

new CheckScoringsNk();