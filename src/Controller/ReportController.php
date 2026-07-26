<?php

namespace App\Controller;

use App\Repository\TransactionRepository;
use App\Repository\ActivityRepository;
use Psr\Log\LoggerInterface;
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
        ActivityRepository $activityRepo,
        LoggerInterface $logger
    ): Response {
        try {
            $periodo = $request->query->get('periodo', 'mes');
            $fechaInicio = $request->query->get('fechaInicio');
            $fechaFin = $request->query->get('fechaFin');
            $actividadId = $request->query->getInt('actividad');
            $categoria = $request->query->get('categoria');
            $tipo = $request->query->get('tipo');
            $page = max(1, $request->getInt('page', 1));

            $now = new \DateTime();
            $startDate = null;
            $endDate = null;

            if ($fechaInicio && $fechaFin && \DateTime::createFromFormat('Y-m-d', $fechaInicio) && \DateTime::createFromFormat('Y-m-d', $fechaFin)) {
                $startDate = \DateTime::createFromFormat('Y-m-d', $fechaInicio);
                $endDate = \DateTime::createFromFormat('Y-m-d', $fechaFin);
                $endDate->setTime(23, 59, 59);
            } else {
                $endDate = (clone $now)->setTime(23, 59, 59);
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
        } catch (\Throwable $e) {
            $logger->error('Error al cargar reportes: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
            ]);

            $totalIncome = 0.0;
            $totalExpenses = 0.0;
            $balance = 0.0;
            $balanceChange = 0;
            $reportData = ['transactions' => [], 'total' => 0, 'page' => 1, 'limit' => 10, 'pages' => 0];
            $activities = [];
            $categories = [];
        }

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
                'periodo' => $periodo ?? 'mes',
                'fechaInicio' => $fechaInicio ?? null,
                'fechaFin' => $fechaFin ?? null,
                'actividad' => $actividadId ?? 0,
                'categoria' => $categoria ?? null,
                'tipo' => $tipo ?? null,
            ],
        ]);
    }
}
