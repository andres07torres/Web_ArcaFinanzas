<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ActivityController extends AbstractController
{
    #[Route('/actividades', name: 'app_actividades')]
    public function index(): Response
    {
        return $this->render('actividades.html.twig', [
            'user' => $this->getUser(),
        ]);
    }
}
