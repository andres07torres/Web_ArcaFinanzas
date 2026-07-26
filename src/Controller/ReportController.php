<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ReportController extends AbstractController
{
    #[Route('/reportes', name: 'app_reportes')]
    public function index(): Response
    {
        return $this->render('reportes.html.twig', [
            'user' => $this->getUser(),
        ]);
    }
}
