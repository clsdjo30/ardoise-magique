<?php

declare(strict_types=1);

namespace App\Form\CarteWizard;

use App\Entity\PlatCategorie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SectionEntryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('categorie', EntityType::class, [
                'class' => PlatCategorie::class,
                'choice_label' => 'titre',
                'label' => 'Catégorie de la section',
                'placeholder' => 'Choisir une catégorie...',
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description (optionnelle)',
                'required' => false,
                'attr' => [
                    'rows' => 2,
                    'placeholder' => 'Description de la section',
                    'class' => 'form-control',
                ],
            ])
            ->add('position', HiddenType::class, [
                'attr' => ['class' => 'section-position'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CarteSectionData::class,
        ]);
    }
}
