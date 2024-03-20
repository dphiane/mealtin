<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\RegisterType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ReservationRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;

#[Route('admin')]
#[IsGranted('ROLE_ADMIN', message: 'You are not allowed to access the admin dashboard.')]
class UserCrudController extends AbstractController
{
    #[Route('/users', name: 'app_admin_users')]
    public function index(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();
        return $this->render('admin/users_crud/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/user-details/{id}', name: 'app_admin_user_details')]
    public function details($id, UserRepository $userRepository, ReservationRepository $reservationRepository, PaginatorInterface $paginator, Request $request)
    {

        $user = $userRepository->findOneBy(['id' => $id]);
        $reservations = $reservationRepository->findBy(['user' => $user]);

        usort($reservations, function ($a, $b) {
            return $b->getDate() <=> $a->getDate();
        });
        $reservationsPaginate = $paginator->paginate(
            $reservations,
            $request->query->getInt('page', 1),
            15 // Définir le nombre de réservations par page
        );

        return $this->render('admin/users_crud/user_details.html.twig', [
            'user' => $user,
            'reservations' => $reservationsPaginate
        ]);
    }

    #[Route('/user-edit/{id}', name: 'app_admin_user_edit')]
    public function edit(UserRepository $userRepository, $id, Request $request)
    {
        $user = $userRepository->findOneBy(['id' => $id]);
        $form = $this->createForm(RegisterType::class, $user);
        $form->remove('password');
        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
        }

        return $this->render('admin/users_crud/edit.html.twig', [
            'form' => $form,
            'user' => $user
        ]);
    }

    #[Route('/supprimer-le-compte/{id}', name: 'app_admin_user_delete')]
    public function cancelReservation($id, EntityManagerInterface $entityManagerInterface,UserRepository $userRepository)
    {
        $user = $userRepository->findOneBy(['id' => $id]);
        
        // Vérifier si la réservation existe
        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        // Supprimer l'entité de la base de données
        $entityManagerInterface->remove($user);
        $entityManagerInterface->flush();

        // Répondre avec une réponse de succès
        $this->addFlash('success', 'L\'utilisateur a bien été supprimer');
        return $this->redirectToRoute('app_admin_users');
    }
}
