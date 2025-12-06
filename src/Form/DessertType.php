<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Dessert;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class DessertType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'attr' => [
                    'placeholder' => 'Ex: Foie gras mi-cuit',
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le nom est obligatoire',
                    ]),
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Ex: Accompagné de son chutney de figues',
                    'class' => 'form-control',
                    'rows' => 3,
                ],
            ])
            ->add('price', NumberType::class, [
                'label' => 'Prix (€)',
                'scale' => 2,
                'required' => false,
                'attr' => [
                    'placeholder' => '15.50',
                    'class' => 'form-control',
                    'step' => '0.01',
                ],
                'help' => 'Saisissez le prix avec deux décimales (ex: 15.50)',
            ]);
            // Le champ position est géré automatiquement par drag & drop
            // Il n'est pas affiché dans le formulaire
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Dessert::class,
        ]);
    }
}
