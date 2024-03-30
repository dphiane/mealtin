<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ErrorController extends AbstractController
{
    #[Route('/{url}', name: 'not_found', requirements: ['url' => '.+'])]
    public function notFound(): Response
    {
        return $this->render('error/index.html.twig');
    }
}
