<?php

namespace App\Tests\Service;

use App\Entity\Activity;
use App\Entity\Transaction;
use App\Enum\TransactionTypeEnum;
use App\Repository\ActivityRepository;
use App\Repository\TransactionRepository;
use App\Service\TransactionService;
use PHPUnit\Framework\TestCase;

class TransactionServiceTest extends TestCase
{
    public function testProcessActivityRaisedAmount(): void
    {
        // Mock repositories
        $transactionRepo = $this->createMock(TransactionRepository::class);
        $activityRepo = $this->createMock(ActivityRepository::class);

        // Create instances
        $activity = new Activity();
        $activity->setRaisedAmount('0');

        $transaction = new Transaction();
        $transaction->setActivity($activity);

        // Setup mock transactions
        $tx1 = clone $transaction;
        $tx1->setAmount('100.50');
        
        $tx2 = clone $transaction;
        $tx2->setAmount('50.25');

        $transactionRepo->expects($this->once())
            ->method('findBy')
            ->with([
                'activity' => $activity,
                'type' => TransactionTypeEnum::INCOME
            ])
            ->willReturn([$tx1, $tx2]);

        $activityRepo->expects($this->once())
            ->method('save')
            ->with($activity, true);

        // Execute service
        $service = new TransactionService($transactionRepo, $activityRepo);
        $service->processActivityRaisedAmount($transaction);

        // Assert
        $this->assertEquals('150.75', $activity->getRaisedAmount());
    }
}
