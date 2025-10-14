<?php

namespace App\Services;

use App\Core\Application\Session\Session as AppSession;
use App\Dto\ExtraServiceVisibilityDto;
use App\Repositories\DoctorConditionRepository;
use App\Repositories\DoctorReturnLogRepository;
use App\Repositories\OracleReturnLogRepository;
use App\Contracts\ExtraServiceInterface;
use Exception;

class ReturnExtraService implements ExtraServiceInterface
{
    private $session;
    private $users;
    private $safetyFlowService;
    private $doctorRepo;
    private $oracleRepo;
    private $conditionRepo;
    private $riskGroupService;

    /**
     * @param AppSession                $session
     * @param \Users                    $users
     * @param SafetyFlowService         $safetyFlowService
     * @param DoctorReturnLogRepository $doctorRepo
     * @param OracleReturnLogRepository $oracleRepo
     * @param DoctorConditionRepository $conditionRepo
     * @param RiskGroupService          $riskGroupService
     */
    public function __construct(
        AppSession                $session,
        \Users                    $users,
        SafetyFlowService         $safetyFlowService,
        DoctorReturnLogRepository $doctorRepo,
        OracleReturnLogRepository $oracleRepo,
        DoctorConditionRepository $conditionRepo,
        RiskGroupService          $riskGroupService
    ) {
        $this->session = $session;
        $this->users = $users;
        $this->safetyFlowService = $safetyFlowService;
        $this->doctorRepo = $doctorRepo;
        $this->oracleRepo = $oracleRepo;
        $this->conditionRepo = $conditionRepo;
        $this->riskGroupService = $riskGroupService;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function checkVisibility(int $user_id): array
    {
        $userId = $user_id ?? ($this->session->isActive() ? (int)$this->session->get('user_id') : null);

        $user = $this->users->get_user($userId);
        if (!$user) {
            return $this->createVisibilityDto(false, false)->toArray();
        }

        $cfg = config('services.extra_service');

        $isNew = empty($user->loan_history);
        $isRiskGroup = $this->riskGroupService->isInRiskGroup($user);
        $isFirstLoanSafe = $this->safetyFlowService->isFirstLoanSafeFlow($user);
        $isUnderSafePeriod = $this->users->isSafetyFlow($user);

        // пороги из конфига
        $days30 = $cfg['return_threshold_days']['both'];
        $days90 = $cfg['return_threshold_days']['financial_doctor'];

        // возвраты
        $returns30 = $this->doctorRepo->countByUser($userId, $days30)
            + $this->oracleRepo->countByUser($userId, $days30);
        $returns90 = $this->doctorRepo->countByUser($userId, $days90);

        // Новый клиент
        if ($isNew) {
            if (!$isUnderSafePeriod) {
                return $this->createVisibilityDto(false, false)->toArray();
            }

            return $this->createVisibilityDto(true, true)->toArray();
        }

        // Возвраты по любой услуге за 30 дней (ФД или Оракул)
        if ($returns30 > 0) {
            return $this->createVisibilityDto(true, false)->toArray();
        }

        // Возвраты по ФД за 90 дней
        if ($returns90 > 0) {
            return $this->createVisibilityDto(true, false)->toArray();
        }

        // Первый займ по опасному флоу (ПК)
        if (!$isFirstLoanSafe) {
            if ($isRiskGroup) {
                // Группа риска
                return $this->createVisibilityDto(false, false)->toArray();
            }

            return $this->createVisibilityDto(false, false)->toArray();
        }

        // Первый займ по безопасному флоу (ПК)
        if ($isUnderSafePeriod) {
            return $this->createVisibilityDto(false, true)->toArray();
        } else {
            return $this->createVisibilityDto(false, false)->toArray();
        }
    }


    /**
     * Выбор цены по ТЗ.
     *
     * @inheritDoc
     * @throws Exception
     */
    public function getServicePrice(int $amount, bool $isNewClient = true, $user_id = null): ?object
    {
        $userId = $user_id ?? ($this->session->isActive() ? (int)$this->session->get('user_id') : null);

        if ($userId === null) {
            return $this->conditionRepo->getCreditDoctor($amount, true);
        }

        $user = $this->users->get_user($userId);

        $isUnderSafePeriod = $this->users->isSafetyFlow($user);

        // новый клиент
        if ($isNewClient) {
            return $this->conditionRepo->getCreditDoctor($amount, true);
        }

        // группа риска, цены из risk_group_prices
        if ($this->riskGroupService->isInRiskGroup($user)) {
            return $this->conditionRepo->getCreditDoctorByPriceGroup($amount, 'risk_group_prices');
        }

        // постоянный клиент, первый займ по безопасному флоу, цены из safety_flow_prices
        if ($this->safetyFlowService->isFirstLoanSafeFlow($user) && $isUnderSafePeriod) {
            return $this->conditionRepo->getCreditDoctorByPriceGroup($amount, 'safety_flow_prices');
        }

        // остальные пк
        return $this->conditionRepo->getCreditDoctor($amount, false);
    }

    /**
     * @param bool $doctor
     * @param bool $oracle
     * @return ExtraServiceVisibilityDto
     */
    private function createVisibilityDto(bool $doctor, bool $oracle): ExtraServiceVisibilityDto
    {
        return new ExtraServiceVisibilityDto($doctor, $oracle);
    }

}
