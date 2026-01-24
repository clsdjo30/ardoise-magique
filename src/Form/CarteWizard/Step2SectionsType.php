<?php

declare(strict_types=1);

namespace App\Form\CarteWizard;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Step2SectionsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('sections', CollectionType::class, [
            'entry_type' => SectionEntryType::class,
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'label' => false,
            'prototype' => true,
            'prototype_name' => '__section__',
            'attr' => [
                'class' => 'sections-collection',
                'data-controller' => 'collection',
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CarteWizardData::class,
            'validation_groups' => ['step2'],
        ]);
    }
}
