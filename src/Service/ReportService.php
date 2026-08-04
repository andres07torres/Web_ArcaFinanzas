<?php

namespace App\Service;

use App\Enum\TransactionTypeEnum;
use App\Repository\TransactionRepository;

class ReportService
{
    public function __construct(
        private TransactionRepository $transactionRepo,
    ) {
    }

    public function parseDates(string $periodo, ?string $fechaInicio, ?string $fechaFin): array
    {
        $now = new \DateTime();
        $startDate = null;
        $endDate = null;

        if ($fechaInicio || $fechaFin) {
            if ($fechaInicio && \DateTime::createFromFormat('Y-m-d', $fechaInicio)) {
                $startDate = \DateTime::createFromFormat('Y-m-d', $fechaInicio)->setTime(0, 0, 0);
            }
            if ($fechaFin && \DateTime::createFromFormat('Y-m-d', $fechaFin)) {
                $endDate = \DateTime::createFromFormat('Y-m-d', $fechaFin)->setTime(23, 59, 59);
            }
        } else {
            switch ($periodo) {
                case 'dia':
                    $startDate = (clone $now)->setTime(0, 0, 0);
                    $endDate = (clone $now)->setTime(23, 59, 59);
                    break;
                case 'semana':
                    $startDate = (clone $now)->modify('monday this week')->setTime(0, 0, 0);
                    $endDate = (clone $now)->modify('sunday this week')->setTime(23, 59, 59);
                    break;
                case 'todos':
                    $startDate = null;
                    $endDate = null;
                    break;
                case 'mes':
                default:
                    $startDate = (clone $now)->modify('first day of this month')->setTime(0, 0, 0);
                    $endDate = (clone $now)->modify('last day of this month')->setTime(23, 59, 59);
                    break;
            }
        }

        return ['start' => $startDate, 'end' => $endDate];
    }

    public function calculateBalanceMetrics(
        ?\DateTimeInterface $startDate,
        ?\DateTimeInterface $endDate,
        ?int $actividadId,
        ?string $categoria,
    ): array {
        $prevStartDate = null;
        $prevEndDate = null;
        if ($startDate instanceof \DateTime && $endDate instanceof \DateTime) {
            $diff = $startDate->diff($endDate);
            $prevEndDate = (clone $startDate)->modify('-1 second');
            $prevStartDate = (clone $prevEndDate)->modify("-{$diff->days} days")->setTime(0, 0, 0);
        }

        $totalIncome = $this->transactionRepo->getTotalByTypeAndDateRange(TransactionTypeEnum::INCOME, $startDate, $endDate, $actividadId, $categoria);
        $totalExpenses = $this->transactionRepo->getTotalByTypeAndDateRange(TransactionTypeEnum::EXPENSE, $startDate, $endDate, $actividadId, $categoria);
        $balance = $totalIncome - $totalExpenses;

        $prevIncome = $this->transactionRepo->getTotalByTypeAndDateRange(TransactionTypeEnum::INCOME, $prevStartDate, $prevEndDate, $actividadId, $categoria);
        $prevExpenses = $this->transactionRepo->getTotalByTypeAndDateRange(TransactionTypeEnum::EXPENSE, $prevStartDate, $prevEndDate, $actividadId, $categoria);
        $prevBalance = $prevIncome - $prevExpenses;

        $balanceChange = 0;
        if (0 != $prevBalance) {
            $balanceChange = round((($balance - $prevBalance) / abs($prevBalance)) * 100, 1);
        }

        return [
            'totalIncome' => $totalIncome,
            'totalExpenses' => $totalExpenses,
            'balance' => $balance,
            'balanceChange' => $balanceChange,
        ];
    }
}
