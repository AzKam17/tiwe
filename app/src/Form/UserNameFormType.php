<?php

namespace App\Form;

use App\Entity\Role;
use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class UserNameFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'attr' => [
                    'placeholder' => 'Sarah',
                    'class' => 'form-control',
                ],
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom de famille',
                'attr' => [
                    'placeholder' => 'Konan',
                    'class' => 'form-control',
                ],
            ])
            ->add('attachedRoles', EntityType::class, [
                'class' => Role::class,
                'mapped' => false,
                'choice_label' => function(Role $role) {
                    return $role->getName();
                },
                'placeholder' => 'Sélectionnez un rôle',
                'query_builder' => function (\App\Repository\RoleRepository $repo): QueryBuilder {
                    return $repo->createQueryBuilder('r')
                        ->where('r.value != :admin')
                        ->setParameter('admin', 'ROLE_ADMIN')
                        ->orderBy('r.name', 'ASC');
                },
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
