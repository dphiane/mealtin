<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email as contact;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function index(Request $request,MailerInterface $mailer): Response
    {
        $form = $this->createFormBuilder()
            ->add('name', TextType::class,[
                'label'=>'Nom',
            'constraints' => [
                new NotBlank(),
                new Length(['min' => 2,'minMessage'=>'Votre nom est trop court']),
            ]])
            ->add('email', EmailType::class,[
                'label'=>'Email',
            'constraints' => [
                new NotBlank(),
                new Email(['message'=>'Veuillez entrer un email valide']),
            ]
        ])
            ->add('message', TextareaType::class,[
                'label'=>'Message',
                'constraints'=>[
                    new NotBlank(),
                    new Length([
                        'min'=>20, 'minMessage'=>'Votre message est trop court, soyer plus précis.'
                    ])
                ]])
            ->add('send', SubmitType::class,['label'=>'Envoyer',])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // data is an array with "name", "email", and "message" keys
            $data = $form->getData();
            $email = (new contact())
            ->from($data['email'])
            ->to('dphiane@yahoo.fr')
            ->subject("contacte mealtin'Potes")
            ->text($data['message']);
            $mailer->send($email);

            $this->addFlash(
                'success',
                'Votre message a bien été envoyé'
            );

            return $this->redirectToRoute("app_home");
        }
        return $this->render('contact/index.html.twig', [
            'form' => $form,
        ]);
    }
}
