<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CashController extends AbstractController
{
    #[Route('/caja', name: 'app_caja')]
    public function index(): Response
    {
        return $this->render('caja.html.twig', [
            'user' => $this->getUser(),
        ]);
    }
}
