<?php

namespace App\Controller;

use App\Entity\Activity;
use App\Form\ActivityType;
use App\Repository\ActivityRepository;
use App\Repository\TransactionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/actividades')]
class ActivityController extends AbstractController
{
    #[Route('', name: 'app_actividades')]
    public function index(ActivityRepository $activityRepo, TransactionRepository $transactionRepo): Response
    {
        $allActivities = $activityRepo->findAll();
        $activeActivities = $activityRepo->findBy(['status' => 'active'], ['startDate' => 'DESC']);
        $finishedActivities = $activityRepo->findBy(['status' => 'finished'], ['endDate' => 'DESC']);
        $cancelledActivities = $activityRepo->findBy(['status' => 'cancelled'], ['endDate' => 'DESC']);

        $totalRaised = $activityRepo->getTotalRaised();
        $totalGoal = 0;
        foreach ($allActivities as $activity) {
            $totalGoal += (float) $activity->getGoalAmount();
        }

        return $this->render('actividades.html.twig', [
            'user' => $this->getUser(),
            'activities' => $allActivities,
            'active_activities' => $activeActivities,
            'finished_activities' => $finishedActivities,
            'cancelled_activities' => $cancelledActivities,
            'total_raised' => $totalRaised,
            'total_goal' => $totalGoal,
            'active_count' => count($activeActivities),
            'finished_count' => count($finishedActivities),
            'cancelled_count' => count($cancelledActivities),
        ]);
    }

    #[Route('/nueva', name: 'app_actividades_nueva')]
    public function create(Request $request, ActivityRepository $activityRepo): Response
    {
        $activity = new Activity();
        $form = $this->createForm(ActivityType::class, $activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $activity->setStatus('active');
            $activity->setRaisedAmount('0.00');
            $activityRepo->save($activity, true);

            $this->addFlash('success', 'Actividad creada exitosamente.');

            return $this->redirectToRoute('app_actividades');
        }

        return $this->render('actividad_form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Nueva Actividad',
            'user' => $this->getUser(),
        ]);
    }

    #[Route('/{id}/editar', name: 'app_actividades_editar', requirements: ['id' => '\d+'])]
    public function edit(Activity $activity, Request $request, ActivityRepository $activityRepo): Response
    {
        $form = $this->createForm(ActivityType::class, $activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $activityRepo->save($activity, true);

            $this->addFlash('success', 'Actividad actualizada exitosamente.');

            return $this->redirectToRoute('app_actividades');
        }

        return $this->render('actividad_form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Editar Actividad',
            'activity' => $activity,
            'user' => $this->getUser(),
        ]);
    }

    #[Route('/{id}/cerrar', name: 'app_actividades_cerrar', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function close(Activity $activity, ActivityRepository $activityRepo): Response
    {
        $activity->setStatus('finished');
        $activityRepo->save($activity, true);

        $this->addFlash('success', 'Actividad finalizada exitosamente.');

        return $this->redirectToRoute('app_actividades');
    }

    #[Route('/{id}/cancelar', name: 'app_actividades_cancelar', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function cancel(Activity $activity, ActivityRepository $activityRepo): Response
    {
        $activity->setStatus('cancelled');
        $activityRepo->save($activity, true);

        $this->addFlash('success', 'Actividad cancelada.');

        return $this->redirectToRoute('app_actividades');
    }

    #[Route('/{id}/reactivar', name: 'app_actividades_reactivar', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function reactivate(Activity $activity, ActivityRepository $activityRepo): Response
    {
        $activity->setStatus('active');
        $activityRepo->save($activity, true);

        $this->addFlash('success', 'Actividad reactivada.');

        return $this->redirectToRoute('app_actividades');
    }

    #[Route('/{id}/eliminar', name: 'app_actividades_eliminar', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Activity $activity, ActivityRepository $activityRepo): Response
    {
        $activityRepo->remove($activity, true);

        $this->addFlash('success', 'Actividad eliminada.');

        return $this->redirectToRoute('app_actividades');
    }
}
