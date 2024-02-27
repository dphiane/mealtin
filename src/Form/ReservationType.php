<?php

namespace App\Form;

use DateTimeImmutable;
use App\Entity\Reservation;
use DateTime;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;


class ReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date', TextType::class, [
                'attr' => ['data-provide' => "datepicker"],
                'data' => date('Y-m-d')
            ])
            // Ajouter un événement de formulaire pour modifier la valeur de 'date'
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                $data = $event->getData();

                // Vérifiez si la date est présente dans les données soumises
                if (isset($data['date'])) {
                    // Modifier la valeur de 'date' string to DateTimeImmutable
                    $dateString = $data['date'];
                    $date = new DateTimeImmutable($dateString);
                    $data['date'] = $date;
                    $event->setData($data);
                }
            })
            //->add('date') décommenter et désactiver jquery et le add->('date') plus haut pour gérer les résa via symfony et pas js.
            
            ->add('time', TimeType::class, [
                'label' => 'Heure',
                'input' => 'datetime_immutable',
                'widget' => 'choice',
            ])
            ->add('howManyGuest', ChoiceType::class, [
                'label' => 'Nombre de personnes',
                'choices' => [
                    "1 couvert" => 1,
                    "2 couverts" => 2,
                    "3 couverts" => 3,
                    "4 couverts" => 4,
                    "5 couverts" => 5,
                    "6 couverts" => 6,
                    "7 couverts" => 7,
                    "8 couverts" => 8,
                    "9 couverts" => 9,
                ]
                ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
        ]);
    }
}
