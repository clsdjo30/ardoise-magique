<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\OpeningHour;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OpeningHoursFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $existingHours = $options['existing_hours'];

        foreach (OpeningHour::DAYS as $dayNum => $dayName) {
            $dayData = $existingHours[$dayNum] ?? [
                'closed' => true,
                'morning_opens' => null,
                'morning_closes' => null,
                'evening_opens' => null,
                'evening_closes' => null
            ];

            $builder->add("day_{$dayNum}_closed", CheckboxType::class, [
                'label' => 'Fermé',
                'required' => false,
                'data' => $dayData['closed'],
                'attr' => [
                    'class' => 'day-closed-checkbox',
                    'data-day' => $dayNum
                ]
            ]);

            $builder->add("day_{$dayNum}_morning_opens", TimeType::class, [
                'label' => 'Ouverture midi',
                'required' => false,
                'widget' => 'single_text',
                'data' => $dayData['morning_opens'],
                'attr' => [
                    'class' => 'form-control time-input',
                    'data-day' => $dayNum
                ]
            ]);

            $builder->add("day_{$dayNum}_morning_closes", TimeType::class, [
                'label' => 'Fermeture midi',
                'required' => false,
                'widget' => 'single_text',
                'data' => $dayData['morning_closes'],
                'attr' => [
                    'class' => 'form-control time-input',
                    'data-day' => $dayNum
                ]
            ]);

            $builder->add("day_{$dayNum}_evening_opens", TimeType::class, [
                'label' => 'Ouverture soir',
                'required' => false,
                'widget' => 'single_text',
                'data' => $dayData['evening_opens'],
                'attr' => [
                    'class' => 'form-control time-input',
                    'data-day' => $dayNum
                ]
            ]);

            $builder->add("day_{$dayNum}_evening_closes", TimeType::class, [
                'label' => 'Fermeture soir',
                'required' => false,
                'widget' => 'single_text',
                'data' => $dayData['evening_closes'],
                'attr' => [
                    'class' => 'form-control time-input',
                    'data-day' => $dayNum
                ]
            ]);
        }

        // Transform flat form data to structured array
        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $hours = [];

            foreach (OpeningHour::DAYS as $dayNum => $dayName) {
                $hours[$dayNum] = [
                    'closed' => $data["day_{$dayNum}_closed"] ?? false,
                    'morning_opens' => $data["day_{$dayNum}_morning_opens"] ?? null,
                    'morning_closes' => $data["day_{$dayNum}_morning_closes"] ?? null,
                    'evening_opens' => $data["day_{$dayNum}_evening_opens"] ?? null,
                    'evening_closes' => $data["day_{$dayNum}_evening_closes"] ?? null,
                ];
            }

            $event->setData(['hours' => $hours]);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'existing_hours' => [],
            'restaurant' => null
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'opening_hours';
    }
}
