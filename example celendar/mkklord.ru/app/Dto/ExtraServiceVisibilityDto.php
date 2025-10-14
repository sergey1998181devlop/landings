<?php

namespace App\Dto;

class ExtraServiceVisibilityDto
{
    /**
     * @var bool
     */
    private $financialDoctor;

    /**
     * @var bool
     */
    private $starOracle;

    public function __construct(bool $financialDoctor, bool $starOracle)
    {
        $this->financialDoctor = $financialDoctor;
        $this->starOracle = $starOracle;
    }

    public function toArray(): array
    {
        return [
            'financial_doctor' => $this->financialDoctor,
            'star_oracle' => $this->starOracle
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['financial_doctor'] ?? false,
            $data['star_oracle'] ?? false
        );
    }
} 