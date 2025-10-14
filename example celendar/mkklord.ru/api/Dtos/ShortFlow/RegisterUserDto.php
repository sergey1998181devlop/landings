<?php
declare(strict_types=1);
final class RegisterUserDto
{
    public string $firstname;
    public string $lastname;
    public string $patronymic;
    /** @var string В формате d.m.Y */
    public string $birth;
    /** @var string Отформатированный телефон, только цифры, начинается с 7 */
    public string $phone_mobile;
    public string $first_loan_amount;
    public string $first_loan_period;
    public string $utm_source;
    public string $utm_medium;
    public string $utm_campaign;
    public string $utm_content;
    public string $utm_term;
    public string $webmaster_id;
    public string $click_hash;
    public string $reg_ip;
    public string $sms;

    public function __construct(
        string $firstname,
        string $lastname,
        string $patronymic,
        string $birth,
        string $phone_mobile,

        string $first_loan_amount,
        string $first_loan_period,
        string $utm_source,
        string $utm_medium,
        string $utm_campaign,
        string $utm_content,
        string $utm_term,
        string $webmaster_id,
        string $click_hash,

        string $reg_ip,
        string $sms
        //todo first_loan_period
        //todo first_load_amount
        //todo utm
    )
    {
        $this->sms = $sms;
        $this->reg_ip = $reg_ip;
        $this->click_hash = $click_hash;
        $this->webmaster_id = $webmaster_id;
        $this->utm_term = $utm_term;
        $this->utm_content = $utm_content;
        $this->utm_campaign = $utm_campaign;
        $this->utm_medium = $utm_medium;
        $this->utm_source = $utm_source;
        $this->first_loan_period = $first_loan_period;
        $this->first_loan_amount = $first_loan_amount;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->patronymic = $patronymic;
        $this->birth = $birth;
        $this->phone_mobile = $phone_mobile;
    }
}