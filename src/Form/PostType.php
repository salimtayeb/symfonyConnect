<?php

namespace App\Form;

use App\Entity\Post;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('description', TextareaType::class, [
                'label' => 'Contenu du post',
                'attr' => [
                    'placeholder' => 'Exprime-toi...',
                ],
            ])
            ->add('lieu', TextType::class, [
                'required' => false,
                'label' => 'Lieu',
            ])
            ->add('image', TextType::class, [
                'required' => false,
                'label' => 'Image (URL)',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
            'csrf_protection' => false,
        ]);
    }
}