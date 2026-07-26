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
            ->add('state', ChoiceType::class, [
                'label' => 'Provincia',
                'required' => false,
                'placeholder' => 'Selecciona una Provincia...',
                'choices' => [
                    'Azuay' => 'Azuay',
                    'Bolívar' => 'Bolívar',
                    'Cañar' => 'Cañar',
                    'Carchi' => 'Carchi',
                    'Chimborazo' => 'Chimborazo',
                    'Cotopaxi' => 'Cotopaxi',
                    'El Oro' => 'El Oro',
                    'Esmeraldas' => 'Esmeraldas',
                    'Galápagos' => 'Galápagos',
                    'Guayas' => 'Guayas',
                    'Imbabura' => 'Imbabura',
                    'Loja' => 'Loja',
                    'Los Ríos' => 'Los Ríos',
                    'Manabí' => 'Manabí',
                    'Morona Santiago' => 'Morona Santiago',
                    'Napo' => 'Napo',
                    'Orellana' => 'Orellana',
                    'Pastaza' => 'Pastaza',
                    'Pichincha' => 'Pichincha',
                    'Santa Elena' => 'Santa Elena',
                    'Santo Domingo de los Tsáchilas' => 'Santo Domingo de los Tsáchilas',
                    'Sucumbíos' => 'Sucumbíos',
                    'Tungurahua' => 'Tungurahua',
                    'Zamora Chinchipe' => 'Zamora Chinchipe',
                ],
                'attr' => [
                    'class' => 'w-full border-outline-variant focus:border-primary focus:ring-primary rounded-lg text-body-md py-2 px-md bg-white',
                ],
                'label_attr' => ['class' => 'font-label-bold text-label-bold text-on-surface-variant uppercase ml-1'],
            ])
            ->add('city', ChoiceType::class, [
                'label' => 'Ciudad',
                'required' => false,
                'placeholder' => 'Selecciona una Ciudad...',
                'choices' => [
                    'Pichincha' => [
                        'Quito' => 'Quito',
                        'Sangolquí' => 'Sangolquí',
                        'Cayambe' => 'Cayambe',
                        'Machachi' => 'Machachi',
                    ],
                    'Guayas' => [
                        'Guayaquil' => 'Guayaquil',
                        'Durán' => 'Durán',
                        'Milagro' => 'Milagro',
                        'Daule' => 'Daule',
                        'Samborondón' => 'Samborondón',
                    ],
                    'Azuay' => [
                        'Cuenca' => 'Cuenca',
                        'Gualaceo' => 'Gualaceo',
                        'Paute' => 'Paute',
                    ],
                    'Manabí' => [
                        'Portoviejo' => 'Portoviejo',
                        'Manta' => 'Manta',
                        'Chone' => 'Chone',
                        'Montecristi' => 'Montecristi',
                        'Jipijapa' => 'Jipijapa',
                    ],
                    'Tungurahua' => [
                        'Ambato' => 'Ambato',
                        'Baños de Agua Santa' => 'Baños de Agua Santa',
                        'Pelileo' => 'Pelileo',
                    ],
                    'El Oro' => [
                        'Machala' => 'Machala',
                        'Pasaje' => 'Pasaje',
                        'Santa Rosa' => 'Santa Rosa',
                        'Huaquillas' => 'Huaquillas',
                    ],
                    'Santo Domingo de los Tsáchilas' => [
                        'Santo Domingo' => 'Santo Domingo',
                    ],
                    'Santa Elena' => [
                        'Santa Elena' => 'Santa Elena',
                        'La Libertad' => 'La Libertad',
                        'Salinas' => 'Salinas',
                    ],
                    'Loja' => [
                        'Loja' => 'Loja',
                        'Catamayo' => 'Catamayo',
                        'Calvas' => 'Calvas',
                    ],
                    'Imbabura' => [
                        'Ibarra' => 'Ibarra',
                        'Otavalo' => 'Otavalo',
                        'Cotacachi' => 'Cotacachi',
                    ],
                    'Chimborazo' => [
                        'Riobamba' => 'Riobamba',
                        'Guano' => 'Guano',
                        'Alausí' => 'Alausí',
                    ],
                    'Cotopaxi' => [
                        'Latacunga' => 'Latacunga',
                        'Salcedo' => 'Salcedo',
                        'Pujilí' => 'Pujilí',
                    ],
                    'Los Ríos' => [
                        'Babahoyo' => 'Babahoyo',
                        'Quevedo' => 'Quevedo',
                        'Buena Fe' => 'Buena Fe',
                        'Ventanas' => 'Ventanas',
                    ],
                    'Esmeraldas' => [
                        'Esmeraldas' => 'Esmeraldas',
                        'Quinindé' => 'Quinindé',
                        'Atacames' => 'Atacames',
                    ],
                    'Carchi' => [
                        'Tulcán' => 'Tulcán',
                        'San Gabriel' => 'San Gabriel',
                    ],
                    'Cañar' => [
                        'Azogues' => 'Azogues',
                        'La Troncal' => 'La Troncal',
                    ],
                    'Bolívar' => [
                        'Guaranda' => 'Guaranda',
                        'San Miguel' => 'San Miguel',
                    ],
                    'Sucumbíos' => [
                        'Nueva Loja (Lago Agrio)' => 'Nueva Loja (Lago Agrio)',
                        'Shushufindi' => 'Shushufindi',
                    ],
                    'Napo' => [
                        'Tena' => 'Tena',
                        'Archidona' => 'Archidona',
                    ],
                    'Orellana' => [
                        'El Coca (Puerto Francisco de Orellana)' => 'El Coca (Puerto Francisco de Orellana)',
                    ],
                    'Pastaza' => [
                        'Puyo' => 'Puyo',
                        'Mera' => 'Mera',
                    ],
                    'Morona Santiago' => [
                        'Macas' => 'Macas',
                        'Gualaquiza' => 'Gualaquiza',
                    ],
                    'Zamora Chinchipe' => [
                        'Zamora' => 'Zamora',
                        'Yantzaza' => 'Yantzaza',
                    ],
                    'Galápagos' => [
                        'Puerto Baquerizo Moreno' => 'Puerto Baquerizo Moreno',
                        'Puerto Ayora' => 'Puerto Ayora',
                    ],
                ],
                'attr' => [
                    'class' => 'w-full border-outline-variant focus:border-primary focus:ring-primary rounded-lg text-body-md py-2 px-md bg-white',
                ],
                'label_attr' => ['class' => 'font-label-bold text-label-bold text-on-surface-variant uppercase ml-1'],
            ])
            ->add('zipCode', ChoiceType::class, [
                'label' => 'Código Postal',
                'required' => false,
                'placeholder' => 'Selecciona un Código Postal...',
                'choices' => [
                    'Pichincha - 170150 (Quito Centro / Sur)' => '170150',
                    'Pichincha - 170501 (Quito Norte)' => '170501',
                    'Pichincha - 170601 (Cumbayá / Tumbaco / Sangolquí)' => '170601',
                    'Guayas - 090150 (Guayaquil Centro)' => '090150',
                    'Guayas - 090501 (Guayaquil Norte / Samborondón)' => '090501',
                    'Guayas - 090901 (Durán / Milagro)' => '090901',
                    'Azuay - 010150 (Cuenca)' => '010150',
                    'Manabí - 130150 (Portoviejo / Manta)' => '130150',
                    'Tungurahua - 180150 (Ambato)' => '180150',
                    'El Oro - 070150 (Machala)' => '070150',
                    'Loja - 110150 (Loja)' => '110150',
                    'Imbabura - 100150 (Ibarra / Otavalo)' => '100150',
                    'Chimborazo - 060150 (Riobamba)' => '060150',
                    'Cotopaxi - 050150 (Latacunga)' => '050150',
                    'Los Ríos - 120150 (Babahoyo / Quevedo)' => '120150',
                    'Esmeraldas - 080150 (Esmeraldas)' => '080150',
                    'Santo Domingo - 230150 (Santo Domingo)' => '230150',
                    'Santa Elena - 240150 (Santa Elena / Salinas)' => '240150',
                    'Carchi - 040150 (Tulcán)' => '040150',
                    'Cañar - 030150 (Azogues)' => '030150',
                    'Bolívar - 020150 (Guaranda)' => '020150',
                    'Sucumbíos - 210150 (Nueva Loja / Lago Agrio)' => '210150',
                    'Napo - 150150 (Tena)' => '150150',
                    'Orellana - 220150 (El Coca)' => '220150',
                    'Pastaza - 160150 (Puyo)' => '160150',
                    'Morona Santiago - 140150 (Macas)' => '140150',
                    'Zamora Chinchipe - 190150 (Zamora)' => '190150',
                    'Galápagos - 200150 (Puerto Baquerizo Moreno / Ayora)' => '200150',
                ],
                'attr' => [
                    'class' => 'w-full border-outline-variant focus:border-primary focus:ring-primary rounded-lg text-body-md py-2 px-md bg-white',
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
