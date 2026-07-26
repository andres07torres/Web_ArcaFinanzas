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

            $totalIncome = $transactionRepo->getTotalByTypeAndDateRange('income', $startDate, $endDate, $actividadId ?: null, $categoria ?: null);
            $totalExpenses = $transactionRepo->getTotalByTypeAndDateRange('expense', $startDate, $endDate, $actividadId ?: null, $categoria ?: null);
            $balance = $totalIncome - $totalExpenses;

            $prevIncome = $transactionRepo->getTotalByTypeAndDateRange('income', $prevStartDate, $prevEndDate, $actividadId ?: null, $categoria ?: null);
            $prevExpenses = $transactionRepo->getTotalByTypeAndDateRange('expense', $prevStartDate, $prevEndDate, $actividadId ?: null, $categoria ?: null);
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

    #[Route('/reportes/exportar/excel', name: 'app_reportes_exportar_excel')]
    public function exportExcel(Request $request, TransactionRepository $transactionRepo): Response
    {
        $fechaInicio = $request->query->get('fechaInicio');
        $fechaFin = $request->query->get('fechaFin');
        $actividadId = $request->query->getInt('actividad');
        $categoria = $request->query->get('categoria');
        $tipo = $request->query->get('tipo');
        $periodo = $request->query->get('periodo', 'mes');

        $dates = $this->parseDates($periodo, $fechaInicio, $fechaFin);
        $reportData = $transactionRepo->getReportTransactions(
            $dates['start'],
            $dates['end'],
            $actividadId ?: null,
            $categoria ?: null,
            $tipo ?: null,
            1,
            10000
        );

        $transactions = $reportData['transactions'];

        $csvData = "\xEF\xBB\xBF";
        $csvData .= "ID;Fecha;Descripción;Tipo;Categoría;Actividad;Monto;Método de Pago;Registrado Por;Comprobante\n";

        foreach ($transactions as $tx) {
            $tipoText = $tx->getType() === 'income' ? 'Ingreso' : 'Egreso';
            $fecha = $tx->getTransactionDate() ? $tx->getTransactionDate()->format('d/m/Y') : '';
            $desc = '"' . str_replace('"', '""', $tx->getDescription()) . '"';
            $cat = '"' . str_replace('"', '""', $tx->getCategory() ?? 'Sin categoría') . '"';
            $act = '"' . str_replace('"', '""', $tx->getActivity() ? $tx->getActivity()->getName() : '---') . '"';
            $monto = number_format((float)$tx->getAmount(), 2, '.', '');
            $metodo = '"' . str_replace('"', '""', $tx->getPaymentMethod() ?? '---') . '"';
            $usuario = '"' . str_replace('"', '""', $tx->getCreatedBy() ? $tx->getCreatedBy()->getFullName() : '---') . '"';
            $comprobante = $tx->getReceiptFilename() ? 'Adjunto' : 'Sin comprobante';

            $csvData .= "{$tx->getId()};{$fecha};{$desc};{$tipoText};{$cat};{$act};{$monto};{$metodo};{$usuario};{$comprobante}\n";
        }

        $filename = 'informe_transparencia_arcafinanzas_' . (new \DateTime())->format('Ymd_His') . '.csv';

        $response = new Response($csvData);
        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        return $response;
    }

    #[Route('/reportes/exportar/pdf', name: 'app_reportes_exportar_pdf')]
    public function exportPdf(Request $request, TransactionRepository $transactionRepo): Response
    {
        $fechaInicio = $request->query->get('fechaInicio');
        $fechaFin = $request->query->get('fechaFin');
        $actividadId = $request->query->getInt('actividad');
        $categoria = $request->query->get('categoria');
        $tipo = $request->query->get('tipo');
        $periodo = $request->query->get('periodo', 'mes');

        $dates = $this->parseDates($periodo, $fechaInicio, $fechaFin);
        $totalIncome = $transactionRepo->getTotalByTypeAndDateRange('income', $dates['start'], $dates['end'], $actividadId ?: null, $categoria ?: null);
        $totalExpenses = $transactionRepo->getTotalByTypeAndDateRange('expense', $dates['start'], $dates['end'], $actividadId ?: null, $categoria ?: null);
        $balance = $totalIncome - $totalExpenses;

        $reportData = $transactionRepo->getReportTransactions(
            $dates['start'],
            $dates['end'],
            $actividadId ?: null,
            $categoria ?: null,
            $tipo ?: null,
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

    private function parseDates(string $periodo, ?string $fechaInicio, ?string $fechaFin): array
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
}
