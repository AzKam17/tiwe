<?php

namespace App\Form;

use App\Entity\InventoryEntry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
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
                'label' => 'Quantit�',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'La quantit� est obligatoire',
                    ]),
                    new Assert\Positive([
                        'message' => 'La quantit� doit �tre sup�rieure � 0',
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
                        'message' => 'Le prix doit �tre positif',
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
            ->add('imageFile', FileType::class, [
                'label' => 'Image personnalisée',
                'required' => false,
                'mapped' => false,
                'constraints' => [
                    new Assert\File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/jpg',
                            'image/png',
                            'image/webp',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPG, PNG, WEBP)',
                    ]),
                ],
                'attr' => [
                    'accept' => 'image/*',
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
