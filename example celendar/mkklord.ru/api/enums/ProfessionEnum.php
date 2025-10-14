<?php

namespace api\enums;

require_once __DIR__  . '/AbstractEnum.php';

class ProfessionEnum extends AbstractEnum
{
    private const SPECIALIST = 'Специалист';
    private const SENIOR_SPECIALIST = 'Старший специалист';
    private const MANAGER = 'Руководитель';

    public static function getAvailableValues(): array
    {
        return [
            self::SPECIALIST,
            self::SENIOR_SPECIALIST,
            self::MANAGER
        ];
    }

    public static function specialist(): self
    {
        return new self(self::SPECIALIST);
    }

    public static function manager(): self
    {
        return new self(self::MANAGER);
    }

    public static function senior_specialist(): self
    {
        return new self(self::SENIOR_SPECIALIST);
    }
}