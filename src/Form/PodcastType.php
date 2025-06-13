<?php

namespace App\Form;

use App\Entity\Podcast;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class PodcastType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre du podcast',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Saisissez le titre du podcast'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 5,
                    'placeholder' => 'Description détaillée du podcast'
                ]
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type de podcast',
                'choices' => [
                    'Audio' => 'audio',
                    'Vidéo' => 'video'
                ],
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('duration', TextType::class, [
                'label' => 'Durée',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: 1h 30min ou 90 minutes'
                ]
            ])
            ->add('audioFile', FileType::class, [
                'label' => 'Fichier audio/vidéo',
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'class' => 'form-control-file'
                ],
                'constraints' => [
                    new File([
                        'maxSize' => '100M',
                        'mimeTypes' => [
                            'audio/mpeg',
                            'audio/mp4',
                            'audio/wav',
                            'video/mp4',
                            'video/mpeg',
                            'video/quicktime',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger un fichier audio/vidéo valide',
                    ])
                ],
            ])
            ->add('externalUrl', UrlType::class, [
                'label' => 'Lien externe (YouTube, SoundCloud, etc.)',
                'required' => false,
                'default_protocol' => 'https',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'https://youtube.com/watch?v=...'
                ]
            ])
            ->add('thumbnail', FileType::class, [
                'label' => 'Image de couverture',
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'class' => 'form-control-file'
                ],
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/webp',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPEG, PNG, WebP)',
                    ])
                ],
            ])
            ->add('isPublished', CheckboxType::class, [
                'label' => 'Publier le podcast',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input'
                ]
            ])
            ->add('isFeatured', CheckboxType::class, [
                'label' => 'Podcast à la une',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input'
                ]
            ])
            ->add('tags', TextType::class, [
                'label' => 'Tags',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Mots-clés séparés par des virgules'
                ]
            ])
            ->add('language', ChoiceType::class, [
                'label' => 'Langue',
                'choices' => [
                    'Français' => 'fr',
                    'Arabe' => 'ar',
                    'Anglais' => 'en'
                ],
                'attr' => [
                    'class' => 'form-control'
                ]
            ]);

        // Transformer pour les tags (array <-> string)
        $builder->get('tags')
            ->addModelTransformer(new CallbackTransformer(
                function ($tagsArray) {
                    // Transforme l'array en string pour l'affichage
                    return is_array($tagsArray) ? implode(', ', $tagsArray) : '';
                },
                function ($tagsString) {
                    // Transforme la string en array pour l'entité
                    if (empty($tagsString)) {
                        return [];
                    }
                    return array_map('trim', explode(',', $tagsString));
                }
            ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Podcast::class,
        ]);
    }
}