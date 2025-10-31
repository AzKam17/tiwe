<?php

namespace App\Form;

use App\Entity\InventoryEntry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class InventoryEntryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('quantity', NumberType::class, [
                'label' => 'Quantité',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'La quantité est obligatoire',
                    ]),
                    new Assert\Positive([
                        'message' => 'La quantité doit être supérieure à 0',
                    ]),
                ],
                'attr' => [
                    'min' => '0.01',
                    'step' => '0.01',
                ],
            ])
            ->add('price', NumberType::class, [
                'label' => 'Prix unitaire',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le prix est obligatoire',
                    ]),
                    new Assert\PositiveOrZero([
                        'message' => 'Le prix doit être positif',
                    ]),
                ],
                'attr' => [
                    'min' => '0',
                    'step' => '0.01',
                ],
            ])
            ->add('notes', TextareaType::class, [
                'label' => 'Notes',
                'required' => false,
                'attr' => [
                    'rows' => 3,
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => InventoryEntry::class,
        ]);
    }
}
