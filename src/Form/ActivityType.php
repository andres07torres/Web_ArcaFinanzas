<?php

namespace App\Form;

use App\Entity\Activity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ActivityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'attr' => [
                    'class' => 'w-full px-md py-md border border-outline-variant rounded-xl bg-surface focus:ring-2 focus:ring-primary focus:border-primary text-body-sm',
                    'placeholder' => 'Nombre de la actividad',
                ],
                'label_attr' => ['class' => 'font-label-bold text-label-bold text-on-surface-variant mb-xs block'],
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'w-full px-md py-md border border-outline-variant rounded-xl bg-surface focus:ring-2 focus:ring-primary focus:border-primary text-body-sm',
                    'rows' => 3,
                    'placeholder' => 'Descripción de la actividad...',
                ],
                'label_attr' => ['class' => 'font-label-bold text-label-bold text-on-surface-variant mb-xs block'],
            ])
            ->add('category', ChoiceType::class, [
                'choices' => [
                    'Recaudación' => 'recaudacion',
                    'Misión' => 'mision',
                    'Social' => 'social',
                    'Juvenil' => 'juvenil',
                    'Infantil' => 'infantil',
                    'Música' => 'musica',
                    'Construcción' => 'construccion',
                    'Otro' => 'otro',
                ],
                'attr' => [
                    'class' => 'w-full px-md py-md border border-outline-variant rounded-xl bg-surface focus:ring-2 focus:ring-primary focus:border-primary text-body-sm',
                ],
                'label_attr' => ['class' => 'font-label-bold text-label-bold text-on-surface-variant mb-xs block'],
                'placeholder' => 'Seleccionar categoría',
                'required' => false,
            ])
            ->add('goalAmount', NumberType::class, [
                'attr' => [
                    'class' => 'w-full px-md py-md border border-outline-variant rounded-xl bg-surface focus:ring-2 focus:ring-primary focus:border-primary text-body-sm',
                    'placeholder' => '0.00',
                    'step' => '0.01',
                    'min' => '0',
                ],
                'label_attr' => ['class' => 'font-label-bold text-label-bold text-on-surface-variant mb-xs block'],
                'scale' => 2,
            ])
            ->add('startDate', DateType::class, [
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'w-full px-md py-md border border-outline-variant rounded-xl bg-surface focus:ring-2 focus:ring-primary focus:border-primary text-body-sm',
                ],
                'label_attr' => ['class' => 'font-label-bold text-label-bold text-on-surface-variant mb-xs block'],
                'required' => false,
            ])
            ->add('endDate', DateType::class, [
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'w-full px-md py-md border border-outline-variant rounded-xl bg-surface focus:ring-2 focus:ring-primary focus:border-primary text-body-sm',
                ],
                'label_attr' => ['class' => 'font-label-bold text-label-bold text-on-surface-variant mb-xs block'],
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Activity::class,
        ]);
    }
}
