<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\OpeningHour;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OpeningHourType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dayOfWeek', ChoiceType::class, [
                'label' => 'Jour',
                'choices' => array_flip(OpeningHour::DAYS),
                'placeholder' => 'Choisir un jour',
                'attr' => ['class' => 'form-select']
            ])
            ->add('opensAt', TimeType::class, [
                'label' => 'Ouverture',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control']
            ])
            ->add('closesAt', TimeType::class, [
                'label' => 'Fermeture',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control']
            ])
            ->add('label', TextType::class, [
                'label' => 'Service',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: Service du midi'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OpeningHour::class,
        ]);
    }
}
