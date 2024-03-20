<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class RegisterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email',EmailType::class)
            ->add('firstname',null,['label'=>'Prénom'])
            ->add('lastname',null,['label'=>'Nom'])
            ->add('telephone',TelType::class,['label'=>'Numéro de téléphone'])
            ->add('password', RepeatedType::class, [
            'type' => PasswordType::class,
            'invalid_message' => 'Les mots de passe ne sont pas identiques',
            'options' => ['attr' => ['class' => 'password-field'], 'toggle' => true, 'hidden_label' => 'Masquer',
                'visible_label' => 'Afficher',],
            'required' => true,
            'first_options'  => ['label' => 'Mot de passe'],
            'second_options' => ['label' => 'Confirmer votre mot de passe'],
            ])
/*             ->add('roles', ChoiceType::class, [
                'choices'  => [
                    'Utilisateur' => 'ROLE_USER',
                    'Valideur' => 'ROLE_VALIDEUR',
                    'Administrateur' => 'ROLE_ADMIN'
                ],
                'multiple' => true, // permet à l'utilisateur de choisir plusieurs rôles
                'expanded' => true, // affiche les choix sous forme de boutons radio ou de cases à cocher
            ]) */
            ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
