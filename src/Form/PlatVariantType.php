<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\PlatVariant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlatVariantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('label', TextType::class, [
                'label' => 'Libellé de la variante',
                'attr' => [
                    'placeholder' => 'Ex: Format Standard, Format XL, À emporter',
                    'class' => 'form-control',
                ],
            ])
            ->add('priceCents', NumberType::class, [
                'label' => 'Prix (en centimes)',
                'attr' => [
                    'placeholder' => 'Ex: 1500 pour 15.00€',
                    'class' => 'form-control',
                ],
                'help' => 'Entrez le prix en centimes (ex: 1500 = 15.00€)',
            ])
            ->add('tvaRate', TextType::class, [
                'label' => 'Taux de TVA',
                'attr' => [
                    'placeholder' => 'Ex: 10.00',
                    'class' => 'form-control',
                ],
            ])
            ->add('isDefault', CheckboxType::class, [
                'label' => 'Variante par défaut',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PlatVariant::class,
        ]);
    }
}
