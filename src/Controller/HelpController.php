<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HelpController extends AbstractController
{
    #[Route('/ayuda', name: 'app_ayuda')]
    public function index(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $asunto = trim((string) $request->request->get('asunto'));
            $mensaje = trim((string) $request->request->get('mensaje'));

            if ($asunto && $mensaje) {
                $this->addFlash('success', 'Tu consulta ha sido enviada exitosamente al soporte técnico del desarrollador. Te responderemos a la brevedad.');

                return $this->redirectToRoute('app_ayuda');
            }
            $this->addFlash('error', 'Por favor completa todos los campos del formulario de soporte.');
        }

        return $this->render('ayuda.html.twig', [
            'user' => $this->getUser(),
        ]);
    }
}
