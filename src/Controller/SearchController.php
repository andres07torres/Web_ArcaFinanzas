<?php

namespace App\Controller;

use App\Enum\TransactionTypeEnum;
use App\Repository\ActivityRepository;
use App\Repository\MemberRepository;
use App\Repository\TransactionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class SearchController extends AbstractController
{
    #[Route('/api/buscar', name: 'app_buscar_api', methods: ['GET'])]
    public function search(
        Request $request,
        TransactionRepository $transactionRepo,
        MemberRepository $memberRepo,
        ActivityRepository $activityRepo,
    ): JsonResponse {
        $q = trim((string) $request->query->get('q', ''));

        if (mb_strlen($q) < 2) {
            return $this->json([
                'query' => $q,
                'total' => 0,
                'results' => [],
            ]);
        }

        $results = [];

        // 1. Transacciones
        $transactions = $transactionRepo->searchTransactions($q, 5);
        foreach ($transactions as $tx) {
            $results[] = [
                'type' => 'transaccion',
                'category' => 'Caja y Finanzas',
                'icon' => TransactionTypeEnum::INCOME === $tx->getType() ? 'arrow_upward' : 'arrow_downward',
                'icon_bg' => TransactionTypeEnum::INCOME === $tx->getType() ? 'bg-secondary-container text-on-secondary-container' : 'bg-error-container text-on-error-container',
                'title' => $tx->getDescription(),
                'subtitle' => (TransactionTypeEnum::INCOME === $tx->getType() ? '+' : '-').'$'.number_format((float) $tx->getAmount(), 2).' · '.($tx->getCategory() ?? 'General'),
                'date' => $tx->getTransactionDate() ? $tx->getTransactionDate()->format('d/m/Y') : '',
                'url' => $this->generateUrl('app_caja'),
            ];
        }

        // 2. Miembros
        $members = $memberRepo->searchMembers($q);
        $members = array_slice($members, 0, 5);
        foreach ($members as $member) {
            $results[] = [
                'type' => 'miembro',
                'category' => 'Directorio de Miembros',
                'icon' => 'person',
                'icon_bg' => 'bg-primary-container text-on-primary-container',
                'title' => $member->getFullName(),
                'subtitle' => ($member->getEmail() ?? 'Sin correo').($member->getPhone() ? ' · '.$member->getPhone() : ''),
                'date' => $member->getRole() ?? 'Miembro',
                'url' => $this->generateUrl('app_directorio').'?selected='.$member->getId(),
            ];
        }

        // 3. Actividades
        $activities = $activityRepo->searchActivities($q, 5);
        foreach ($activities as $act) {
            $results[] = [
                'type' => 'actividad',
                'category' => 'Eventos y Actividades',
                'icon' => 'event',
                'icon_bg' => 'bg-surface-container-high text-primary',
                'title' => $act->getName(),
                'subtitle' => $act->getDescription() ? mb_strimwidth($act->getDescription(), 0, 50, '...') : 'Meta: $'.number_format((float) $act->getGoalAmount(), 2),
                'date' => $act->getStartDate() ? $act->getStartDate()->format('d/m/Y') : '',
                'url' => $this->generateUrl('app_actividades'),
            ];
        }

        return $this->json([
            'query' => $q,
            'total' => count($results),
            'results' => $results,
        ]);
    }
}
