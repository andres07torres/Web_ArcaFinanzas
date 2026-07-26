<?php

namespace App\Form;

use App\Entity\Member;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MemberType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'attr' => [
                    'class' => 'w-full border-outline-variant focus:border-primary focus:ring-primary rounded-lg text-body-md py-2 px-md',
                    'placeholder' => 'ej. Juan',
                ],
                'label_attr' => ['class' => 'font-label-bold text-label-bold text-on-surface-variant uppercase ml-1'],
            ])
            ->add('lastName', TextType::class, [
                'attr' => [
                    'class' => 'w-full border-outline-variant focus:border-primary focus:ring-primary rounded-lg text-body-md py-2 px-md',
                    'placeholder' => 'ej. Pérez',
                ],
                'label_attr' => ['class' => 'font-label-bold text-label-bold text-on-surface-variant uppercase ml-1'],
            ])
            ->add('email', EmailType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'w-full border-outline-variant focus:border-primary focus:ring-primary rounded-lg text-body-md py-2 px-md',
                    'placeholder' => 'correo@ejemplo.com',
                ],
                'label_attr' => ['class' => 'font-label-bold text-label-bold text-on-surface-variant uppercase ml-1'],
            ])
            ->add('phone', TelType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'w-full border-outline-variant focus:border-primary focus:ring-primary rounded-lg text-body-md py-2 px-md',
                    'placeholder' => '(555) 000-0000',
                ],
                'label_attr' => ['class' => 'font-label-bold text-label-bold text-on-surface-variant uppercase ml-1'],
            ])
            ->add('role', ChoiceType::class, [
                'choices' => [
                    'Miembro' => 'miembro',
                    'Diácono' => 'diacono',
                    'Anciano' => 'anciano',
                    'Pastor' => 'pastor',
                    'Colaborador' => 'colaborador',
                    'Visitante' => 'visitante',
                ],
                'required' => false,
                'attr' => [
                    'class' => 'w-full border-outline-variant focus:border-primary focus:ring-primary rounded-lg text-body-md py-2 px-md',
                ],
                'label_attr' => ['class' => 'font-label-bold text-label-bold text-on-surface-variant uppercase ml-1'],
                'placeholder' => 'Seleccionar rol',
            ])
            ->add('joinDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'attr' => [
                    'class' => 'w-full border-outline-variant focus:border-primary focus:ring-primary rounded-lg text-body-md py-2 px-md',
                ],
                'label_attr' => ['class' => 'font-label-bold text-label-bold text-on-surface-variant uppercase ml-1'],
            ])
            ->add('address', TextareaType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'w-full border-outline-variant focus:border-primary focus:ring-primary rounded-lg text-body-md py-2 px-md',
                    'rows' => 2,
                    'placeholder' => 'Dirección completa...',
                ],
                'label_attr' => ['class' => 'font-label-bold text-label-bold text-on-surface-variant uppercase ml-1'],
            ])
            ->add('city', TextType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'w-full border-outline-variant focus:border-primary focus:ring-primary rounded-lg text-body-md py-2 px-md',
                    'placeholder' => 'Ciudad',
                ],
                'label_attr' => ['class' => 'font-label-bold text-label-bold text-on-surface-variant uppercase ml-1'],
            ])
            ->add('state', TextType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'w-full border-outline-variant focus:border-primary focus:ring-primary rounded-lg text-body-md py-2 px-md',
                    'placeholder' => 'Estado/Provincia',
                ],
                'label_attr' => ['class' => 'font-label-bold text-label-bold text-on-surface-variant uppercase ml-1'],
            ])
            ->add('zipCode', TextType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'w-full border-outline-variant focus:border-primary focus:ring-primary rounded-lg text-body-md py-2 px-md',
                    'placeholder' => 'Código Postal',
                ],
                'label_attr' => ['class' => 'font-label-bold text-label-bold text-on-surface-variant uppercase ml-1'],
            ])
            ->add('notes', TextareaType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'w-full border-outline-variant focus:border-primary focus:ring-primary rounded-lg text-body-md py-2 px-md',
                    'rows' => 3,
                    'placeholder' => 'Notas adicionales sobre el miembro...',
                ],
                'label_attr' => ['class' => 'font-label-bold text-label-bold text-on-surface-variant uppercase ml-1'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Member::class,
        ]);
    }
}
