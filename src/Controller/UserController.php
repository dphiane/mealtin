<?php

namespace App\Controller;

use App\Form\RegisterType;
use App\Form\ReservationType;
use App\Repository\DisponibilityRepository;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function currentUserProfile(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $userPasswordHasherInterface,ReservationRepository $reservationRepository): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $userForm = $this->createForm(RegisterType::class, $user);
        $userForm->remove('password');
        $userForm->add('newPassword', PasswordType::class, ['label' => 'Nouveau mot de passe', 'required' => false]);
        $userForm->handleRequest($request);
        if ($userForm->isSubmitted() && $userForm->isValid()) {
            $newPassword = $user->getNewPassword();
            if ($newPassword) {
                $hash = $userPasswordHasherInterface->hashPassword($user, $newPassword);
                $user->setPassword($hash);
            }
            $em->flush();
            $this->addFlash('success', 'Modification sauvegardées');
            return $this->redirectToRoute('app_current_user');
        }
        $reservations=$reservationRepository->findBy(['user'=> $user->getId()]);
        return $this->render('user/index.html.twig', [
            'form' => $userForm->createView(),
            'reservations' =>$reservations
        ]);
    }
    #[Route('/user/edit/{id}', name: 'app_user_edit')]
    public function editReservation($id,ReservationRepository $reservationRepository, Request $request,DisponibilityRepository $disponibilityRepository,EntityManagerInterface $entityManagerInterface)
    {
        $reservation = $reservationRepository->findOneBy(['id' => $id]);
        $reservationForm = $this->createForm(ReservationType::class,$reservation);
        $reservationForm->handleRequest($request);

        if($reservationForm->isSubmitted() && $reservationForm->isValid()){
            
        }
        return $this->render('user/edit.html.twig',[
            'reservationForm'=>$reservationForm->createView(),
        ]);
        
    }
    #[Route('/user/{id}', name: 'app_user_cancel')]
    public function cancelReservation($id)
    {
        return $this->render('user/edit.html.twig');
    }
}
