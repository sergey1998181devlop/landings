<?php
declare(strict_types=1);
final class AddOrderDto
{
    public int $user_id;
    public string $ip;
    public string $juicescore_session_id;
    public string $utm_source;
    public string $utm_medium;
    public string $utm_campaign;
    public string $utm_content;
    public string $utm_term;
    public string $webmaster_id;
    public string $click_hash;

    public function __construct(
        int $user_id,
        string $ip,
        string $juicescore_session_id,
        string $utm_source,
        string $utm_medium,
        string $utm_campaign,
        string $utm_content,
        string $utm_term,
        string $webmaster_id,
        string $click_hash
    )
    {
        $this->user_id = $user_id;
        $this->ip = $ip;
        $this->juicescore_session_id = $juicescore_session_id;
        $this->utm_source = $utm_source;
        $this->utm_medium = $utm_medium;
        $this->utm_campaign = $utm_campaign;
        $this->utm_content = $utm_content;
        $this->utm_term = $utm_term;
        $this->webmaster_id = $webmaster_id;
        $this->click_hash = $click_hash;
    }
}