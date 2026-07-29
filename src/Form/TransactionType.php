<?php

namespace App\Form;

use App\Entity\Activity;
use App\Entity\Transaction;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class TransactionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
                'label' => 'Tipo de Transacción',
                'choices' => [
                    'Ingreso' => 'income',
                    'Gasto' => 'expense',
                ],
                'attr' => [
                    'class' => 'w-full h-12 px-md rounded-lg border border-outline-variant bg-white focus:ring-2 focus:ring-primary',
                ],
                'label_attr' => ['class' => 'font-label-bold text-label-bold text-on-surface-variant'],
                'expanded' => false,
            ])
            ->add('amount', NumberType::class, [
                'label' => 'Monto ($)',
                'attr' => [
                    'class' => 'w-full h-12 pl-md pr-md rounded-lg border border-outline-variant bg-white focus:ring-2 focus:ring-primary focus:border-primary text-headline-sm font-data-mono',
                    'placeholder' => '0.00',
                    'step' => '0.01',
                    'min' => '0.01',
                ],
                'label_attr' => ['class' => 'font-label-bold text-label-bold text-on-surface-variant'],
                'scale' => 2,
            ])
            ->add('transactionDate', DateType::class, [
                'label' => 'Fecha de Transacción',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'w-full h-12 px-md rounded-lg border border-outline-variant bg-white focus:ring-2 focus:ring-primary focus:border-primary',
                ],
                'label_attr' => ['class' => 'font-label-bold text-label-bold text-on-surface-variant'],
            ])
            ->add('paymentMethod', ChoiceType::class, [
                'label' => 'Método de Pago',
                'choices' => [
                    'Efectivo' => 'efectivo',
                    'Transferencia Bancaria' => 'transferencia',
                    'Depósito' => 'deposito',
                    'Cheque' => 'cheque',
                    'Tarjeta de Crédito' => 'tarjeta_credito',
                    'Tarjeta de Débito' => 'tarjeta_debito',
                ],
                'attr' => [
                    'class' => 'w-full h-12 px-md rounded-lg border border-outline-variant bg-white focus:ring-2 focus:ring-primary',
                ],
                'label_attr' => ['class' => 'font-label-bold text-label-bold text-on-surface-variant'],
                'required' => false,
            ])
            ->add('category', ChoiceType::class, [
                'label' => 'Categoría',
                'choices' => [
                    'Diezmos' => 'diezmos',
                    'Ofrendas' => 'ofrendas',
                    'Donaciones' => 'donaciones',
                    'Eventos' => 'eventos',
                    'Mantenimiento' => 'mantenimiento',
                    'Servicios' => 'servicios',
                    'Materiales' => 'materiales',
                    'Salarios' => 'salarios',
                    'Misiones' => 'misiones',
                    'Otro' => 'otro',
                ],
                'attr' => [
                    'class' => 'w-full h-12 px-md rounded-lg border border-outline-variant bg-white focus:ring-2 focus:ring-primary',
                ],
                'label_attr' => ['class' => 'font-label-bold text-label-bold text-on-surface-variant'],
                'required' => false,
            ])
            ->add('activity', EntityType::class, [
                'label' => 'Actividad / Evento',
                'class' => Activity::class,
                'choice_label' => 'name',
                'placeholder' => 'Fondo General',
                'required' => false,
                'attr' => [
                    'class' => 'w-full h-12 px-md rounded-lg border border-outline-variant bg-white focus:ring-2 focus:ring-primary appearance-none',
                ],
                'label_attr' => ['class' => 'font-label-bold text-label-bold text-on-surface-variant'],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Descripción',
                'attr' => [
                    'class' => 'w-full p-md rounded-lg border border-outline-variant bg-white focus:ring-2 focus:ring-primary focus:border-primary',
                    'placeholder' => 'Agregue detalles opcionales sobre esta transacción...',
                    'rows' => 3,
                ],
                'label_attr' => ['class' => 'font-label-bold text-label-bold text-on-surface-variant'],
                'required' => false,
            ])
            ->add('receipt', FileType::class, [
                'label' => 'Comprobante de Pago (Imagen o PDF)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '10M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/webp',
                            'application/pdf',
                        ],
                        'mimeTypesMessage' => 'Por favor sube un comprobante válido (JPG, PNG, WEBP o PDF).',
                    ]),
                ],
                'attr' => [
                    'class' => 'w-full p-sm rounded-lg border border-outline-variant bg-white focus:ring-2 focus:ring-primary',
                    'accept' => 'image/*,.pdf',
                ],
                'label_attr' => ['class' => 'font-label-bold text-label-bold text-on-surface-variant'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Transaction::class,
        ]);
    }
}
