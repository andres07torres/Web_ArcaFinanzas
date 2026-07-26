<?php

namespace App\Controller;

use App\Entity\Member;
use App\Form\MemberType;
use App\Repository\MemberRepository;
use App\Repository\TransactionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/directorio')]
class DirectoryController extends AbstractController
{
    #[Route('', name: 'app_directorio')]
    public function index(Request $request, MemberRepository $memberRepo, TransactionRepository $transactionRepo): Response
    {
        $search = $request->query->get('search', '');
        
        if ($search) {
            $members = $memberRepo->searchMembers($search);
        } else {
            $members = $memberRepo->findActiveMembers();
        }

        $totalMembers = $memberRepo->getMemberCount();
        $activeMembers = $memberRepo->getActiveMemberCount();
        
        $selectedMember = null;
        $memberTransactions = [];
        
        if ($members && count($members) > 0) {
            $selectedId = $request->query->get('id');
            if ($selectedId) {
                // Buscar el miembro en la lista actual
                foreach ($members as $m) {
                    if ($m->getId() == $selectedId) {
                        $selectedMember = $m;
                        break;
                    }
                }
            }
            
            // Si no se encontró o no se pasó ID, seleccionar el primero
            if (!$selectedMember) {
                $selectedMember = $members[0];
            }

            $memberTransactions = $transactionRepo->createQueryBuilder('t')
                ->where('t.createdBy = :member')
                ->setParameter('member', $selectedMember)
                ->orderBy('t.transactionDate', 'DESC')
                ->getQuery()
                ->getResult();
        }

        return $this->render('directorio.html.twig', [
            'user' => $this->getUser(),
            'members' => $members,
            'selected_member' => $selectedMember,
            'member_transactions' => $memberTransactions,
            'total_members' => $totalMembers,
            'active_members' => $activeMembers,
            'search' => $search,
        ]);
    }

    #[Route('/nuevo', name: 'app_directorio_nuevo')]
    public function create(Request $request, MemberRepository $memberRepo): Response
    {
        $member = new Member();
        $form = $this->createForm(MemberType::class, $member);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $member->setStatus('active');
            $member->setUpdatedAt(new \DateTime());
            $memberRepo->save($member, true);

            $this->addFlash('success', 'Miembro registrado exitosamente.');

            return $this->redirectToRoute('app_directorio');
        }

        return $this->render('miembro_form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Registrar Miembro',
            'user' => $this->getUser(),
        ]);
    }

    #[Route('/{id}/editar', name: 'app_directorio_editar', requirements: ['id' => '\d+'])]
    public function edit(Member $member, Request $request, MemberRepository $memberRepo): Response
    {
        /** @var \App\Entity\User|null $user */
        $user = $this->getUser();
        if (!$user || ($user->getEmail() !== $member->getEmail() && !in_array($user->getRegistrationRole(), ['tesorero', 'administrador']))) {
            $this->addFlash('error', 'No tienes permiso para editar el perfil de este miembro.');
            return $this->redirectToRoute('app_directorio');
        }

        $form = $this->createForm(MemberType::class, $member);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $member->setUpdatedAt(new \DateTime());
            $memberRepo->save($member, true);

            $this->addFlash('success', 'Miembro actualizado exitosamente.');

            return $this->redirectToRoute('app_directorio');
        }

        return $this->render('miembro_form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Editar Miembro',
            'member' => $member,
            'user' => $this->getUser(),
        ]);
    }

    #[Route('/{id}/eliminar', name: 'app_directorio_eliminar', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Member $member, MemberRepository $memberRepo): Response
    {
        $memberRepo->remove($member, true);

        $this->addFlash('success', 'Miembro eliminado del directorio.');

        return $this->redirectToRoute('app_directorio');
    }

    #[Route('/{id}/desactivar', name: 'app_directorio_desactivar', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function deactivate(Member $member, MemberRepository $memberRepo): Response
    {
        $member->setStatus('inactive');
        $member->setUpdatedAt(new \DateTime());
        $memberRepo->save($member, true);

        $this->addFlash('success', 'Miembro desactivado.');

        return $this->redirectToRoute('app_directorio');
    }

    #[Route('/{id}/reactivar', name: 'app_directorio_reactivar', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function reactivate(Member $member, MemberRepository $memberRepo): Response
    {
        $member->setStatus('active');
        $member->setUpdatedAt(new \DateTime());
        $memberRepo->save($member, true);

        $this->addFlash('success', 'Miembro reactivado.');

        return $this->redirectToRoute('app_directorio');
    }
}
