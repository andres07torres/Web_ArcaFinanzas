<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Enum\TransactionTypeEnum;
use App\Form\TransactionType;
use App\Repository\ActivityRepository;
use App\Repository\TransactionRepository;
use App\Service\TransactionService;
use League\Flysystem\FilesystemOperator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/caja')]
class CashController extends AbstractController
{
    #[Route('', name: 'app_caja')]
    public function index(TransactionRepository $transactionRepo, ActivityRepository $activityRepo): Response
    {
        $totalIncome = $transactionRepo->getTotalByType(TransactionTypeEnum::INCOME);
        $totalExpenses = $transactionRepo->getTotalByType(TransactionTypeEnum::EXPENSE);
        $balance = $transactionRepo->getBalance();
        $recentTransactions = $transactionRepo->findRecentTransactions(10);
        $transactionCount = $transactionRepo->getTransactionCount();
        $activeActivities = $activityRepo->findActiveActivities();

        return $this->render('caja.html.twig', [
            'user' => $this->getUser(),
            'total_income' => $totalIncome,
            'total_expenses' => $totalExpenses,
            'balance' => $balance,
            'recent_transactions' => $recentTransactions,
            'transaction_count' => $transactionCount,
            'active_activities' => $activeActivities,
        ]);
    }

    #[Route('/nueva', name: 'app_caja_nueva')]
    public function create(Request $request, TransactionRepository $transactionRepo, ActivityRepository $activityRepo, FilesystemOperator $supabaseStorage, TransactionService $transactionService): Response
    {
        $transaction = new Transaction();
        $transaction->setTransactionDate(new \DateTime());
        /** @var \App\Entity\User|null $user */
        $user = $this->getUser();
        $transaction->setCreatedBy($user);

        $actividadId = (int) $request->query->get('actividad');
        if ($actividadId) {
            $act = $activityRepo->find($actividadId);
            if ($act) {
                $transaction->setActivity($act);
            }
        }

        $form = $this->createForm(TransactionType::class, $transaction);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Si el formulario HTML manual no envió la actividad, la forzamos desde el parámetro de la URL
            if (!$transaction->getActivity() && $actividadId) {
                $act = $activityRepo->find($actividadId);
                if ($act) {
                    $transaction->setActivity($act);
                }
            }

            /** @var \Symfony\Component\HttpFoundation\File\UploadedFile|null $receiptFile */
            $receiptFile = $form->get('receipt')->getData();
            if ($receiptFile) {
                $newFilename = uniqid('receipt_', true).'.'.$receiptFile->guessExtension();

                try {
                    $contenido = file_get_contents($receiptFile->getPathname());
                    $supabaseStorage->write($newFilename, $contenido);
                    $transaction->setReceiptFilename($newFilename);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Error al guardar el archivo del comprobante.');
                }
            }

            $transactionRepo->save($transaction, true);

            $activity = $transaction->getActivity();
            $transactionService->processActivityRaisedAmount($transaction);

            $this->addFlash('success', 'Transacción registrada exitosamente.');

            if ($activity) {
                return $this->redirectToRoute('app_actividades_detalle', ['id' => $activity->getId()]);
            }

            return $this->redirectToRoute('app_caja');
        }

        return $this->render('transaccion_form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Registrar Transacción',
            'user' => $this->getUser(),
        ]);
    }

    #[Route('/transaccion/{id}/subir-comprobante', name: 'app_caja_subir_comprobante', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function uploadReceipt(Transaction $transaction, Request $request, TransactionRepository $transactionRepo, FilesystemOperator $supabaseStorage): Response
    {
        $csrf = (string) $request->request->get('_csrf_token');
        if (!$this->isCsrfTokenValid('upload_receipt_'.$transaction->getId(), $csrf)) {
            $this->addFlash('error', 'Token CSRF inválido.');

            return $this->redirectToRoute('app_reportes');
        }

        /** @var \Symfony\Component\HttpFoundation\File\UploadedFile|null $receiptFile */
        $receiptFile = $request->files->get('receipt_file');
        if ($receiptFile) {
            $newFilename = uniqid('receipt_', true).'.'.$receiptFile->guessExtension();

            try {
                $contenido = file_get_contents($receiptFile->getPathname());
                $supabaseStorage->write($newFilename, $contenido);
                $transaction->setReceiptFilename($newFilename);
                $transactionRepo->save($transaction, true);
                $this->addFlash('success', 'Comprobante adjuntado correctamente.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Ocurrió un error al subir el comprobante.');
            }
        } else {
            $this->addFlash('error', 'Por favor selecciona un archivo de comprobante válido.');
        }

        $redirect = $request->headers->get('referer') ?? $this->generateUrl('app_reportes');

        return $this->redirect($redirect);
    }

    #[Route('/comprobante/{filename}', name: 'app_caja_ver_comprobante', methods: ['GET'])]
    public function viewReceipt(string $filename, FilesystemOperator $supabaseStorage): Response
    {
        if (!$supabaseStorage->fileExists($filename)) {
            throw $this->createNotFoundException('El comprobante no existe.');
        }

        $stream = $supabaseStorage->readStream($filename);
        $mimeType = $supabaseStorage->mimeType($filename);

        $response = new StreamedResponse(function () use ($stream) {
            fpassthru($stream);
            fclose($stream);
        });

        $response->headers->set('Content-Type', $mimeType);

        return $response;
    }

    #[Route('/{id}/eliminar', name: 'app_caja_eliminar', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Transaction $transaction, TransactionRepository $transactionRepo): Response
    {
        $transactionRepo->remove($transaction, true);

        $this->addFlash('success', 'Transacción eliminada.');

        return $this->redirectToRoute('app_caja');
    }
}
