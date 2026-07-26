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
    private const ALLOWED_ROLES = ['tesorero', 'coordinador', 'subcoordinador', 'sub-coordinador', 'miembro'];

    private const MAX_EMAIL_LENGTH = 180;
    private const MAX_NAME_LENGTH = 255;
    private const MIN_PASSWORD_LENGTH = 8;
    private const MAX_PASSWORD_LENGTH = 128;

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

            $email = trim((string) $request->request->get('email'));
            $password = (string) $request->request->get('password');
            $fullName = trim((string) $request->request->get('full_name'));
            $role = strtolower(trim((string) $request->request->get('role')));
            $terms = $request->request->get('terms');

            if ($email === '' || $password === '' || !$terms) {
                $this->addFlash('error', 'Completa todos los campos requeridos.');
                return $this->redirectToRoute('app_registro');
            }

            if (strlen($email) > self::MAX_EMAIL_LENGTH) {
                $this->addFlash('error', 'El correo electrónico es demasiado largo.');
                return $this->redirectToRoute('app_registro');
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->addFlash('error', 'El correo electrónico no tiene un formato válido.');
                return $this->redirectToRoute('app_registro');
            }

            if (strlen($fullName) > self::MAX_NAME_LENGTH) {
                $this->addFlash('error', 'El nombre es demasiado largo.');
                return $this->redirectToRoute('app_registro');
            }

            if (strlen($password) < self::MIN_PASSWORD_LENGTH) {
                $this->addFlash('error', 'La contraseña debe tener al menos ' . self::MIN_PASSWORD_LENGTH . ' caracteres.');
                return $this->redirectToRoute('app_registro');
            }

            if (strlen($password) > self::MAX_PASSWORD_LENGTH) {
                $this->addFlash('error', 'La contraseña es demasiado larga.');
                return $this->redirectToRoute('app_registro');
            }

            if (!in_array($role, self::ALLOWED_ROLES, true)) {
                $this->addFlash('error', 'El rol seleccionado no es válido.');
                return $this->redirectToRoute('app_registro');
            }

            $existing = $em->getRepository(User::class)->findOneBy(['email' => $email]);
            if ($existing) {
                $this->addFlash('error', 'Este correo ya está registrado.');
                return $this->redirectToRoute('app_registro');
            }

            $user = new User();
            $user->setEmail($email);
            $user->setFullName($fullName !== '' ? $fullName : null);
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
