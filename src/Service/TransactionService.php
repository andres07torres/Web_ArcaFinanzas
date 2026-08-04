<?php

namespace App\Service;

use App\Entity\Transaction;
use App\Enum\TransactionTypeEnum;
use App\Repository\ActivityRepository;
use App\Repository\TransactionRepository;

class TransactionService
{
    public function __construct(
        private TransactionRepository $transactionRepo,
        private ActivityRepository $activityRepo,
    ) {
    }

    public function processActivityRaisedAmount(Transaction $transaction): void
    {
        $activity = $transaction->getActivity();

        if (!$activity) {
            return;
        }

        $activityIncomes = $this->transactionRepo->findBy([
            'activity' => $activity,
            'type' => TransactionTypeEnum::INCOME,
        ]);

        $totalRaised = 0.0;
        foreach ($activityIncomes as $inc) {
            $totalRaised += (float) $inc->getAmount();
        }

        $activity->setRaisedAmount((string) $totalRaised);
        $this->activityRepo->save($activity, true);
    }
}
