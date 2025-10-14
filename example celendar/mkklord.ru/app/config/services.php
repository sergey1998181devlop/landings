<?php

return [
    'extra_service' => [
        'return_tables' => [
            'financial_doctor' => '__credit_doctor_to_user',
            'star_oracle'      => '__star_oracle',
        ],
        'return_status'         => 'SUCCESS',
        'return_threshold_days' => [
            'both'             => 30,
            'financial_doctor' => 90,
            'star_oracle'      => 90,
        ],
    ],
]; 