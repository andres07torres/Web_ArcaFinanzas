<?php

namespace App\Service;

use App\Enum\TransactionTypeEnum;
use League\Csv\Writer;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportService
{
    /**
     * @param iterable $transactions Iterator or array of Transaction objects
     */
    public function exportCsv(iterable $transactions, string $filenamePrefix = 'informe_transparencia_arcafinanzas_'): Response
    {
        $filename = $filenamePrefix.(new \DateTime())->format('Ymd_His').'.csv';

        $response = new StreamedResponse(function () use ($transactions) {
            $csv = Writer::createFromStream(fopen('php://output', 'w+'));
            // Soporte para BOM UTF-8 en Excel
            $csv->setOutputBOM(Writer::BOM_UTF8);
            $csv->setDelimiter(';');

            $csv->insertOne([
                'ID', 'Fecha', 'Descripción', 'Tipo', 'Categoría', 'Actividad', 'Monto', 'Método de Pago', 'Registrado Por', 'Comprobante',
            ]);

            foreach ($transactions as $tx) {
                /** @var \App\Entity\Transaction $tx */
                $tipoText = TransactionTypeEnum::INCOME === $tx->getType() ? 'Ingreso' : 'Egreso';
                $fecha = $tx->getTransactionDate() ? $tx->getTransactionDate()->format('d/m/Y') : '';
                $desc = $tx->getDescription();
                $cat = $tx->getCategory() ?? 'Sin categoría';
                $act = $tx->getActivity() ? $tx->getActivity()->getName() : '---';
                $monto = number_format((float) $tx->getAmount(), 2, '.', '');
                $metodo = $tx->getPaymentMethod() ?? '---';
                $usuario = $tx->getCreatedBy() ? $tx->getCreatedBy()->getFullName() : '---';
                $comprobante = $tx->getReceiptFilename() ? 'Adjunto' : 'Sin comprobante';

                $csv->insertOne([
                    $tx->getId(),
                    $fecha,
                    $desc,
                    $tipoText,
                    $cat,
                    $act,
                    $monto,
                    $metodo,
                    $usuario,
                    $comprobante,
                ]);
            }
        });

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$filename.'"');

        return $response;
    }
}
