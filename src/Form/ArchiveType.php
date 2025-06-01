<?php

namespace App\Form;

use App\Entity\Archive;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ArchiveType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre de l\'archive',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Titre du document historique'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 6,
                    'placeholder' => 'Description détaillée de l\'archive historique'
                ]
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type d\'archive',
                'choices' => [
                    'Article historique' => 'article',
                    'Document audio' => 'audio',
                    'Document vidéo' => 'video',
                    'Manuscrit' => 'manuscript',
                    'Photographie' => 'photo',
                    'Carte/Plan' => 'map'
                ],
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('period', TextType::class, [
                'label' => 'Période historique',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: XIXe siècle, 1800-1900, Empire de Kong'
                ]
            ])
            ->add('originalDate', DateType::class, [
                'label' => 'Date originale du document',
                'required' => false,
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('source', TextType::class, [
                'label' => 'Source/Provenance',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Archives nationales, Collection privée, etc.'
                ]
            ])
            ->add('location', TextType::class, [
                'label' => 'Lieu lié à l\'archive',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Kong, région, ville...'
                ]
            ])
            ->add('archiveFile', FileType::class, [
                'label' => 'Fichier de l\'archive',
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'class' => 'form-control-file'
                ],
                'constraints' => [
                    new File([
                        'maxSize' => '50M',
                        'mimeTypes' => [
                            'application/pdf',
                            'image/jpeg',
                            'image/png',
                            'image/tiff',
                            'audio/mpeg',
                            'audio/wav',
                            'video/mp4',
                            'video/mpeg',
                            'application/msword',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                        ],
                        'mimeTypesMessage' => 'Format non supporté. Utilisez PDF, images, audio, vidéo ou documents Word.',
                    ])
                ],
            ])
            ->add('thumbnail', FileType::class, [
                'label' => 'Image de prévisualisation',
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
                'label' => 'Publier l\'archive',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input'
                ]
            ])
            ->add('isRestricted', CheckboxType::class, [
                'label' => 'Accès restreint (utilisateurs connectés uniquement)',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input'
                ]
            ])
            ->add('tags', TextType::class, [
                'label' => 'Mots-clés',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Kong, Islam, Histoire, Manuscrit, etc.'
                ]
            ])
            ->add('language', ChoiceType::class, [
                'label' => 'Langue du document',
                'choices' => [
                    'Français' => 'fr',
                    'Arabe' => 'ar',
                    'Anglais' => 'en',
                    'Langue locale' => 'local',
                    'Mixte' => 'mixed'
                ],
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('historicalContext', TextareaType::class, [
                'label' => 'Contexte historique',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4,
                    'placeholder' => 'Contexte et importance historique de ce document'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Archive::class,
        ]);
    }
}
