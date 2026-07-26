<?php

namespace App\Controller;

use App\Repository\TransactionRepository;
use App\Repository\ActivityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ReportController extends AbstractController
{
    #[Route('/reportes', name: 'app_reportes')]
    public function index(
        Request $request,
        TransactionRepository $transactionRepo,
        ActivityRepository $activityRepo
    ): Response {
        $periodo = $request->query->get('periodo', 'mes');
        $fechaInicio = $request->query->get('fechaInicio');
        $fechaFin = $request->query->get('fechaFin');
        $actividadId = $request->query->getInt('actividad');
        $categoria = $request->query->get('categoria');
        $tipo = $request->query->get('tipo');
        $page = $request->getInt('page', 1);

        $now = new \DateTime();
        $startDate = null;
        $endDate = null;

        if ($fechaInicio && $fechaFin) {
            $startDate = \DateTime::createFromFormat('Y-m-d', $fechaInicio);
            $endDate = \DateTime::createFromFormat('Y-m-d', $fechaFin);
            if ($endDate) {
                $endDate->setTime(23, 59, 59);
            }
        } else {
            $endDate = $now->setTime(23, 59, 59);
            switch ($periodo) {
                case 'dia':
                    $startDate = (clone $now)->setTime(0, 0, 0);
                    break;
                case 'semana':
                    $startDate = (clone $now)->modify('monday this week')->setTime(0, 0, 0);
                    break;
                case 'mes':
                default:
                    $startDate = (clone $now)->modify('first day of this month')->setTime(0, 0, 0);
                    break;
            }
        }

        $prevStartDate = null;
        $prevEndDate = null;
        if ($startDate && $endDate) {
            $diff = $startDate->diff($endDate);
            $prevEndDate = (clone $startDate)->modify('-1 second');
            $prevStartDate = (clone $prevEndDate)->modify("-{$diff->days} days")->setTime(0, 0, 0);
        }

        $totalIncome = $transactionRepo->getTotalByTypeAndDateRange('income', $startDate, $endDate);
        $totalExpenses = $transactionRepo->getTotalByTypeAndDateRange('expense', $startDate, $endDate);
        $balance = $totalIncome - $totalExpenses;

        $prevIncome = $transactionRepo->getTotalByTypeAndDateRange('income', $prevStartDate, $prevEndDate);
        $prevExpenses = $transactionRepo->getTotalByTypeAndDateRange('expense', $prevStartDate, $prevEndDate);
        $prevBalance = $prevIncome - $prevExpenses;

        $balanceChange = 0;
        if ($prevBalance != 0) {
            $balanceChange = round((($balance - $prevBalance) / abs($prevBalance)) * 100, 1);
        }

        $reportData = $transactionRepo->getReportTransactions(
            $startDate,
            $endDate,
            $actividadId ?: null,
            $categoria ?: null,
            $tipo ?: null,
            $page
        );

        $activities = $activityRepo->findAll();
        $categories = $transactionRepo->getTransactionCategories();

        return $this->render('reportes.html.twig', [
            'user' => $this->getUser(),
            'total_income' => $totalIncome,
            'total_expenses' => $totalExpenses,
            'balance' => $balance,
            'balance_change' => $balanceChange,
            'transactions' => $reportData['transactions'],
            'total_transactions' => $reportData['total'],
            'current_page' => $reportData['page'],
            'total_pages' => $reportData['pages'],
            'limit' => $reportData['limit'],
            'activities' => $activities,
            'categories' => $categories,
            'filters' => [
                'periodo' => $periodo,
                'fechaInicio' => $fechaInicio,
                'fechaFin' => $fechaFin,
                'actividad' => $actividadId,
                'categoria' => $categoria,
                'tipo' => $tipo,
            ],
        ]);
    }
}
