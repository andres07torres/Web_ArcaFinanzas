<?php

namespace App\Controller;

use App\Enum\TransactionTypeEnum;
use App\Repository\ActivityRepository;
use App\Repository\TransactionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    #[Route('/', name: 'app_dashboard')]
    public function index(TransactionRepository $transactionRepo, ActivityRepository $activityRepo): Response
    {
        $user = $this->getUser();
        $totalIncome = $transactionRepo->getTotalByType(TransactionTypeEnum::INCOME);
        $totalExpenses = $transactionRepo->getTotalByType(TransactionTypeEnum::EXPENSE);
        $balance = $transactionRepo->getBalance();
        $recentTransactions = $transactionRepo->findRecentTransactions(5);
        $transactionCount = $transactionRepo->getTransactionCount();
        $activeActivities = $activityRepo->findRecentActivities(3);
        $totalRaised = $activityRepo->getTotalRaised();

        return $this->render('dashboard.html.twig', [
            'user' => $user,
            'total_income' => $totalIncome,
            'total_expenses' => $totalExpenses,
            'balance' => $balance,
            'recent_transactions' => $recentTransactions,
            'transaction_count' => $transactionCount,
            'active_activities' => $activeActivities,
            'total_raised' => $totalRaised,
        ]);
    }
}
