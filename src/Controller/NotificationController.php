<?php

namespace App\Controller;

use App\Repository\ActivityRepository;
use App\Repository\MemberRepository;
use App\Repository\TransactionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class NotificationController extends AbstractController
{
    #[Route('/api/notificaciones', name: 'app_notificaciones_api', methods: ['GET'])]
    public function getNotifications(
        Request $request,
        TransactionRepository $transactionRepo,
        ActivityRepository $activityRepo,
        MemberRepository $memberRepo
    ): JsonResponse {
        $session = $request->getSession();
        $isCleared = $session->get('notifications_cleared', false);

        if ($isCleared) {
            return $this->json([
                'count' => 0,
                'items' => [],
            ]);
        }

        $notifications = [];

        $recentTransactions = $transactionRepo->findBy([], ['id' => 'DESC'], 5);
        foreach ($recentTransactions as $tx) {
            $typeLabel = $tx->getType() === 'income' ? 'Ingreso' : 'Gasto';
            $notifications[] = [
                'type' => 'transaction',
                'title' => 'Nueva transacción (' . $typeLabel . ')',
                'message' => $tx->getDescription() . ' - $' . number_format((float) $tx->getAmount(), 2),
                'date' => $tx->getTransactionDate()?->format('d M, Y') ?? 'Hoy',
                'icon' => $tx->getType() === 'income' ? 'add_circle' : 'remove_circle',
                'color' => $tx->getType() === 'income' ? 'text-secondary' : 'text-error',
                'url' => $this->generateUrl('app_caja'),
            ];
        }

        $recentMembers = $memberRepo->findBy([], ['id' => 'DESC'], 3);
        foreach ($recentMembers as $m) {
            $notifications[] = [
                'type' => 'member',
                'title' => 'Nuevo miembro registrado',
                'message' => $m->getFullName() . ($m->getRole() ? ' (' . $m->getRole() . ')' : ''),
                'date' => $m->getJoinDate()?->format('d M, Y') ?? 'Hoy',
                'icon' => 'person_add',
                'color' => 'text-primary',
                'url' => $this->generateUrl('app_directorio', ['id' => $m->getId()]),
            ];
        }

        $recentActivities = $activityRepo->findBy([], ['id' => 'DESC'], 3);
        foreach ($recentActivities as $act) {
            $notifications[] = [
                'type' => 'activity',
                'title' => 'Evento: ' . $act->getName(),
                'message' => 'Estado: ' . strtoupper((string) $act->getStatus()) . ' - Meta: $' . number_format((float) $act->getGoalAmount(), 2),
                'date' => $act->getStartDate()?->format('d M, Y') ?? 'Hoy',
                'icon' => 'event',
                'color' => 'text-on-secondary-container',
                'url' => $this->generateUrl('app_actividades'),
            ];
        }

        return $this->json([
            'count' => count($notifications),
            'items' => array_slice($notifications, 0, 8),
        ]);
    }

    #[Route('/api/notificaciones/limpiar', name: 'app_notificaciones_limpiar', methods: ['POST'])]
    public function clearNotifications(Request $request): JsonResponse
    {
        $session = $request->getSession();
        $session->set('notifications_cleared', true);

        return $this->json([
            'success' => true,
            'message' => 'Todas las notificaciones han sido marcadas como leídas y limpiadas.',
        ]);
    }
}
