<?php

namespace App\Controller;

use App\Enum\TransactionTypeEnum;
use App\Repository\ActivityRepository;
use App\Repository\TransactionRepository;
use App\Service\ExportService;
use App\Service\ReportService;
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
        ReportService $reportService,
        LoggerInterface $logger,
    ): Response {
        $startDate = null;
        $endDate = null;

        try {
            $periodo = $request->query->get('periodo', 'mes');
            $fechaInicio = $request->query->get('fechaInicio');
            $fechaFin = $request->query->get('fechaFin');
            $actividadId = (int) $request->query->get('actividad');
            $categoria = $request->query->get('categoria');
            $tipoStr = $request->query->get('tipo');
            $tipo = $tipoStr ? TransactionTypeEnum::tryFrom($tipoStr) : null;
            $page = max(1, (int) $request->query->get('page', 1));

            $dates = $reportService->parseDates($periodo, $fechaInicio, $fechaFin);
            $startDate = $dates['start'];
            $endDate = $dates['end'];

            $metrics = $reportService->calculateBalanceMetrics(
                $startDate,
                $endDate,
                $actividadId ?: null,
                $categoria ?: null
            );

            $reportData = $transactionRepo->getReportTransactions(
                $startDate,
                $endDate,
                $actividadId ?: null,
                $categoria ?: null,
                $tipo,
                $page
            );

            $activities = $activityRepo->findAll();
            $categories = $transactionRepo->getTransactionCategories();
        } catch (\Throwable $e) {
            $logger->error('Error al cargar reportes: '.$e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
            ]);

            $metrics = [
                'totalIncome' => 0.0,
                'totalExpenses' => 0.0,
                'balance' => 0.0,
                'balanceChange' => 0,
            ];
            $reportData = ['transactions' => [], 'total' => 0, 'page' => 1, 'limit' => 10, 'pages' => 0];
            $activities = [];
            $categories = [];
        }

        return $this->render('reportes.html.twig', [
            'user' => $this->getUser(),
            'total_income' => $metrics['totalIncome'],
            'total_expenses' => $metrics['totalExpenses'],
            'balance' => $metrics['balance'],
            'balance_change' => $metrics['balanceChange'],
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
                'tipo' => $tipoStr ?? null,
            ],
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    #[Route('/reportes/exportar/excel', name: 'app_reportes_exportar_excel')]
    public function exportExcel(
        Request $request,
        TransactionRepository $transactionRepo,
        ReportService $reportService,
        ExportService $exportService,
    ): Response {
        $fechaInicio = $request->query->get('fechaInicio');
        $fechaFin = $request->query->get('fechaFin');
        $actividadId = (int) $request->query->get('actividad');
        $categoria = $request->query->get('categoria');
        $tipoStr = $request->query->get('tipo');
        $tipo = $tipoStr ? TransactionTypeEnum::tryFrom($tipoStr) : null;
        $periodo = $request->query->get('periodo', 'mes');

        $dates = $reportService->parseDates($periodo, $fechaInicio, $fechaFin);

        $transactionsIterable = $transactionRepo->getReportTransactionsIterable(
            $dates['start'],
            $dates['end'],
            $actividadId ?: null,
            $categoria ?: null,
            $tipo
        );

        return $exportService->exportCsv($transactionsIterable);
    }

    #[Route('/reportes/exportar/pdf', name: 'app_reportes_exportar_pdf')]
    public function exportPdf(
        Request $request,
        TransactionRepository $transactionRepo,
        ReportService $reportService,
    ): Response {
        $fechaInicio = $request->query->get('fechaInicio');
        $fechaFin = $request->query->get('fechaFin');
        $actividadId = (int) $request->query->get('actividad');
        $categoria = $request->query->get('categoria');
        $tipoStr = $request->query->get('tipo');
        $tipo = $tipoStr ? TransactionTypeEnum::tryFrom($tipoStr) : null;
        $periodo = $request->query->get('periodo', 'mes');

        $dates = $reportService->parseDates($periodo, $fechaInicio, $fechaFin);
        $totalIncome = $transactionRepo->getTotalByTypeAndDateRange(TransactionTypeEnum::INCOME, $dates['start'], $dates['end'], $actividadId ?: null, $categoria ?: null);
        $totalExpenses = $transactionRepo->getTotalByTypeAndDateRange(TransactionTypeEnum::EXPENSE, $dates['start'], $dates['end'], $actividadId ?: null, $categoria ?: null);
        $balance = $totalIncome - $totalExpenses;

        $reportData = $transactionRepo->getReportTransactions(
            $dates['start'],
            $dates['end'],
            $actividadId ?: null,
            $categoria ?: null,
            $tipo,
            1,
            10000
        );

        return $this->render('reportes_pdf.html.twig', [
            'transactions' => $reportData['transactions'],
            'total_income' => $totalIncome,
            'total_expenses' => $totalExpenses,
            'balance' => $balance,
            'startDate' => $dates['start'],
            'endDate' => $dates['end'],
        ]);
    }
}
