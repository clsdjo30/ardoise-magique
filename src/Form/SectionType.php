<?php

namespace App\Form;

use App\Entity\Section;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre de la section',
                'attr' => ['placeholder' => 'Ex: Mise en Bouche, Entrée Froide, Poisson...'],
            ])
            ->add('ordre', IntegerType::class, [
                'label' => 'Ordre',
                'help' => 'Position dans le menu (ex: 1, 2, 3...)',
            ])
            ->add('plats', CollectionType::class, [
                'entry_type' => PlatType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => 'Plats de la section',
                'attr' => [
                    'class' => 'plats-collection',
                    'data-controller' => 'collection',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Section::class,
        ]);
    }
}
