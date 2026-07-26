<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class SettingsController extends AbstractController
{
    #[Route('/configuracion', name: 'app_configuracion', methods: ['GET', 'POST'])]
    public function index(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        /** @var User|null $user */
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('error', 'Debes iniciar sesión para acceder a la configuración.');
            return $this->redirectToRoute('app_login');
        }

        if ($request->isMethod('POST')) {
            $csrf = (string) $request->request->get('_csrf_token');
            if (!$this->isCsrfTokenValid('settings_profile', $csrf)) {
                $this->addFlash('error', 'Token CSRF inválido.');
                return $this->redirectToRoute('app_configuracion');
            }

            $fullName = trim((string) $request->request->get('fullName'));
            $email = trim((string) $request->request->get('email'));
            $currentPassword = (string) $request->request->get('currentPassword');
            $newPassword = (string) $request->request->get('newPassword');
            $role = strtolower(trim((string) $request->request->get('role')));

            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->addFlash('error', 'Por favor ingresa un correo electrónico válido.');
                return $this->redirectToRoute('app_configuracion');
            }

            if ($email !== $user->getEmail()) {
                $existing = $em->getRepository(User::class)->findOneBy(['email' => $email]);
                if ($existing && $existing->getId() !== $user->getId()) {
                    $this->addFlash('error', 'El correo electrónico ya está registrado por otro usuario.');
                    return $this->redirectToRoute('app_configuracion');
                }
                $user->setEmail($email);
            }

            $user->setFullName($fullName !== '' ? $fullName : null);
            if (in_array($role, ['tesorero', 'administrador', 'colaborador'], true)) {
                $user->setRegistrationRole($role);
            }

            if ($newPassword !== '') {
                if ($currentPassword === '' || !$passwordHasher->isPasswordValid($user, $currentPassword)) {
                    $this->addFlash('error', 'La contraseña actual no es correcta.');
                    return $this->redirectToRoute('app_configuracion');
                }

                if (strlen($newPassword) < 8) {
                    $this->addFlash('error', 'La nueva contraseña debe tener al menos 8 caracteres.');
                    return $this->redirectToRoute('app_configuracion');
                }

                $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
            }

            $em->flush();
            $this->addFlash('success', 'Perfil y configuración actualizados correctamente.');
            return $this->redirectToRoute('app_configuracion');
        }

        return $this->render('configuracion.html.twig', [
            'user' => $user,
            'active_menu' => 'configuracion',
        ]);
    }
}
