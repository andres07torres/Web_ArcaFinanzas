<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DirectoryController extends AbstractController
{
    #[Route('/directorio', name: 'app_directorio')]
    public function index(): Response
    {
        return $this->render('directorio.html.twig', [
            'user' => $this->getUser(),
        ]);
    }
}
