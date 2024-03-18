<?php

namespace App\Controller\Admin;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('admin')]
class UserCrudController extends AbstractController
{
    #[Route('/user', name: 'app_user_crud')]
    public function index(UserRepository $userRepository): Response
    {
        $users= $userRepository->findAll();
        return $this->render('admin/users_crud/index.html.twig', [
            'users' => $users,
        ]);
    }
}
