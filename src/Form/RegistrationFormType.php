<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // 🔐 User
            ->add('username', TextType::class, [
                'label' => 'Nom d’utilisateur'
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email'
            ])

            // 👤 Profile (non mappé)
            ->add('firstName', TextType::class, [
                'mapped' => false,
                'label' => 'Prénom',
                'constraints' => [
                    new NotBlank(['message' => 'Le prénom est obligatoire']),
                ],
            ])
            ->add('lastName', TextType::class, [
                'mapped' => false,
                'label' => 'Nom',
                'constraints' => [
                    new NotBlank(['message' => 'Le nom est obligatoire']),
                ],
            ])
            ->add('birthDate', DateType::class, [
                'mapped' => false,
                'widget' => 'single_text',
                'label' => 'Date de naissance',
                'constraints' => [
                    new NotBlank(['message' => 'La date de naissance est obligatoire']),
                ],
            ])
            ->add('city', TextType::class, [
                'mapped' => false,
                'label' => 'Ville',
                'constraints' => [
                    new NotBlank(['message' => 'La ville est obligatoire']),
                ],
            ])

            // 🔒 Password
            ->add('plainPassword', PasswordType::class, [
                'mapped' => false,
                'label' => 'Mot de passe',
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un mot de passe',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Minimum {{ limit }} caractères',
                        'max' => 4096,
                    ]),
                ],
            ])

            // ✔️ Conditions
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'label' => "J’accepte les conditions",
                'constraints' => [
                    new IsTrue([
                        'message' => 'Vous devez accepter les conditions.',
                    ]),
                ],
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