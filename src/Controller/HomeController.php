<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class HomeController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('login.html.twig', [
            'error' => $error,
            'last_username' => $lastUsername,
        ]);
    }

    #[Route('/registro', name: 'app_registro', methods: ['GET', 'POST'])]
    public function registro(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        if ($request->isMethod('POST')) {
            $csrf = $request->request->get('_csrf_token');
            if (!$this->isCsrfTokenValid('register', $csrf)) {
                $this->addFlash('error', 'Token CSRF inválido.');
                return $this->redirectToRoute('app_registro');
            }

            $email = $request->request->get('email');
            $password = $request->request->get('password');
            $fullName = $request->request->get('full_name');
            $role = $request->request->get('role');
            $terms = $request->request->get('terms');

            if (!$email || !$password || !$terms) {
                $this->addFlash('error', 'Completa todos los campos requeridos.');
                return $this->redirectToRoute('app_registro');
            }

            $existing = $em->getRepository(User::class)->findOneBy(['email' => $email]);
            if ($existing) {
                $this->addFlash('error', 'Este correo ya está registrado.');
                return $this->redirectToRoute('app_registro');
            }

            $user = new User();
            $user->setEmail($email);
            $user->setFullName($fullName);
            $user->setRegistrationRole($role);
            $user->setPassword($passwordHasher->hashPassword($user, $password));

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Solicitud enviada. Un administrador revisará tu registro.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('registro.html.twig');
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This should never be reached.');
    }
}
