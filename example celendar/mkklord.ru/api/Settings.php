<?php

/**
 * Управление настройками магазина, хранящимися в базе данных
 * В отличие от класса Config оперирует настройками доступными админу и хранящимися в базе данных.
 *
 * @copyright 	2011 Denis Pikusov
 * @link 		http://simplacms.ru
 * @author 		Denis Pikusov
 *
 */

require_once('Simpla.php');

/**
 * @property string $site_name
 * @property string $company_name
 * @property string $theme
 * @property string $products_num
 * @property string $products_num_admin
 * @property string $units
 * @property string $date_format
 * @property string $order_email
 * @property string $comment_email
 * @property string $notify_from_email
 * @property string $decimals_point
 * @property string $thousands_separator
 * @property string $last_1c_orders_export_date
 * @property string $license
 * @property string $max_order_amount
 * @property string $watermark_offset_x
 * @property string $watermark_offset_y
 * @property string $watermark_transparency
 * @property string $images_sharpen
 * @property string $admin_email
 * @property string $pz_server
 * @property string $pz_password
 * @property array $pz_phones
 * @property string $api_password
 * @property array $apikeys
 * @property array $scoring_settings
 * @property string $cdoctor_enabled
 * @property string $verificator_daily_plan_pk
 * @property string $verificator_daily_plan_nk
 * @property string $selenoid
 * @property array $individual_settings
 * @property string $last_update_border_date
 * @property string $recaptcha_key
 * @property string $recaptcha_secret
 * @property string $cc_pr_prolongation_plan
 * @property string $cc_pr_close_plan
 * @property string $next_mobile_version
 * @property array $additional_services_settings
 * @property string $is_CB
 * @property string $is_looker
 * @property array $insurance_threshold_settings
 * @property string $captcha_status
 * @property string $last_session_id
 * @property array $insurance_threshold_setting
 * @property array $notice_sms_approve
 * @property array $auto_approve
 * @property string $sms_approve_status
 * @property string $sms_template_motivation_close_status
 * @property string $percent_insurer_boostra
 * @property string $enable_loan_nk
 * @property array $enable_prolongation_checkbox
 * @property string $fake_try_prolongation_checkbox
 * @property string $gov_auth
 * @property string $enable_b2p_for_nk
 * @property string $b2p_dop_organization
 * @property string $tinkoff_dop_organization
 * @property string $repay_max_count
 * @property string $repay_timeout
 * @property string $ccprolongations1
 * @property string $ccprolongations2
 * @property string $ccprolongations3
 * @property string $ccprolongations4
 * @property string $ccprolongations5
 * @property string $delete_after_01072023
 * @property string $send_cd_date
 * @property array $sum_order_auto_approve
 * @property string $check_redirect_list
 * @property string $likezaim_enabled
 * @property string $site_warning_message_enabled
 * @property string $site_warning_message
 * @property string $addresses_is_dadata
 * @property string $dbrain_auto
 * @property string $autoapprove_plus_30
 * @property string $split_test_users
 * @property string $hui
 * @property string $installment_test_users
 * @property string $enabled_5days_maratorium
 * @property string $installments_enabled
 * @property string $addcard_rejected_enabled
 * @property string $cross_orders_enabled
 * @property string $pdn_sync_day
 * @property string $safe_flow Вкл/выкл безопасное флоу
 * @property string $unsafe_flow Вкл/выкл опасное флоу
 * @property string $new_flow_enabled Включено ли новое флоу с УПРИДом
 * @property string $leadgid_scorista_enabled Включена ли таблица с настройками минимального проходного балла для лидгенов
 * @property string $sbp_enabled
 * @property string $check_reports_for_loans_enable Включена ли проверка актуальности ССП и КИ отчетов при выдаче займов
 * @property string $auto_confirm_for_auto_approve_orders_enable Включено ли авто-подтверждение (с отправкой смс с АСП-кодом) авто-одобренных заявок
 * @property array $prolongation_visible Настройки видимости баннера пролонгации для бакетов с -5 по 0
 * @property array $prolongation_text Переопределение текста баннера пролонгации для бакетов с -5 по 0
 * @property string $display_policy_days - Количество дней через которое отображаются полисы
 * @property string $bonon_enabled Включена ли продажа карт отказных НК клиентов
 * @property array $bonon_sources Продаваемые в Bonon источники
 * @property string $short_flow_enabled Короткое флоу регистрации включено
 * @property string $need_notify_user_when_scorista_success Нужно ли уведомлять пользовать об одобрении скористы
 * @property boolean $registration_disabled_captcha Выключена ли капча на странице входа и регистрации нового пользователя
 * @property array $flow_after_personal_data Настройки флоу телефон после ФИО + паспорт
 * @property array $autoconfirm_flow_utm_sources Utm метки для потока трафика НК автовыдачи
 * @property string $axi_spr_enabled Часть потока ориентируется только на решение Акси, скориста не делает автоотказ и не ставит суммы в заявках
 * @property string $notice_contact_me_enabled Включён ли нотис "Свяжитесь со мной" в ЛК?
 * @property string $notice_contact_me_enabled_for Нотис "Свяжитесь со мной" в ЛК доступен для: 1 - ПК, 2 - НК, 3 - Всех?
 * @property array $t_bank_button_registration Кнопка T-Bank на регистрации
 * @property boolean $faq_highlight_enabled Флаг активации подсветки раздела "Вопросы и ответы" в ЛК
 * @property int $faq_highlight_delay Задержка в минутах до подсветки раздела "Вопросы и ответы" в ЛК
 */
class Settings extends Simpla
{
	private $vars = array();

    const CONTACT_ME_NOTICE_ENABLED_FOR_REPEAT_CLIENTS = 1;
    const CONTACT_ME_NOTICE_ENABLED_FOR_NEW_CLIENTS = 2;
    const CONTACT_ME_NOTICE_ENABLED_FOR_ALL = 3;

	function __construct()
	{
		parent::__construct();
		
		// Выбираем из базы настройки
		$this->db->query('SELECT name, value FROM __settings');

		// и записываем их в переменную		
		foreach($this->db->results() as $result)
			if(!($this->vars[$result->name] = @unserialize($result->value)))
				$this->vars[$result->name] = $result->value;
        
        
        if (!empty($_COOKIE['theme']))
        {
            $this->vars['theme'] = 'akticom';
        }
    }
	
	public function __get($name)
	{
		if($res = parent::__get($name))
			return $res;
		
		if(isset($this->vars[$name]))
			return $this->vars[$name];
		else
			return null;
	}
	
	public function __set($name, $value)
	{
		$this->vars[$name] = $value;

		if(is_array($value))
			$value = serialize($value);
		else
			$value = (string) $value;
			
		$this->db->query('SELECT count(*) as count FROM __settings WHERE name=?', $name);
		if($this->db->result('count')>0)
			$this->db->query('UPDATE __settings SET value=? WHERE name=?', $value, $name);
		else
			$this->db->query('INSERT INTO __settings SET value=?, name=?', $value, $name);
	}
}
