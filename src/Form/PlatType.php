<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Plat;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

class PlatType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom du plat',
                'attr' => [
                    'placeholder' => 'Ex: Velouté de Potimarron',
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le nom du plat est obligatoire',
                    ]),
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Ex: et ses éclats de châtaigne',
                    'class' => 'form-control',
                    'rows' => 2,
                ],
            ])
            ->add('prix', MoneyType::class, [
                'label' => 'Prix (€)',
                'currency' => 'EUR',
                'attr' => [
                    'placeholder' => '12.50',
                    'class' => 'form-control',
                    'step' => '0.01',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le prix est obligatoire',
                    ]),
                    new PositiveOrZero([
                        'message' => 'Le prix doit être positif',
                    ]),
                ],
            ])
            ->add('ordre', NumberType::class, [
                'label' => 'Ordre',
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0,
                ],
                'data' => ($options['data'] && $options['data']->getOrdre()) ? $options['data']->getOrdre() : 0,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Plat::class,
        ]);
    }
}
