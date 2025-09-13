<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class CheckEmailFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('email', EmailType::class, [
            'label' => 'Adresse e-mail',
            'constraints' => [
                new Assert\NotBlank([
                    'message' => "L'adresse e-mail est obligatoire."
                ]),
                new Assert\Email([
                    'message' => "Veuillez entrer une adresse e-mail valide."
                ]),
            ],
            'attr' => [
                'placeholder' => 'exemple@mail.com',
                'class' => 'form-control',
            ],
        ]);
    }
}
