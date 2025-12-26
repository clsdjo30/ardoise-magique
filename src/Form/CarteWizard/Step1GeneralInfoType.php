<?php

declare(strict_types=1);

namespace App\Form\CarteWizard;

use App\Entity\Restaurant;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Step1GeneralInfoType extends AbstractType
{
    public function __construct(
        private Security $security
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var User $user */
        $user = $this->security->getUser();

        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de la carte',
                'required' => true,
                'help' => 'Donnez un nom à votre carte pour la différencier',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: Carte d\'été, Menu de Noël...',
                ],
            ])
            ->add('validFrom', DateType::class, [
                'label' => 'Date de début',
                'widget' => 'single_text',
                'required' => true,
                'help' => 'À partir de quelle date cette carte sera-t-elle valide ?',
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('validTo', DateType::class, [
                'label' => 'Date de fin (optionnelle)',
                'widget' => 'single_text',
                'required' => false,
                'help' => 'Laissez vide pour une validité illimitée',
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('restaurant', EntityType::class, [
                'class' => Restaurant::class,
                'choice_label' => 'name',
                'label' => 'Restaurant',
                'required' => true,
                'choices' => $user->getRestaurants(),
                'placeholder' => 'Sélectionnez un restaurant',
                'attr' => [
                    'class' => 'form-control',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CarteWizardData::class,
            'validation_groups' => ['step1'],
        ]);
    }
}
