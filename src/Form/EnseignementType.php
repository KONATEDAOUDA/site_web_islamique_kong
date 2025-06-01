<?php

namespace App\Form;

use App\Entity\Enseignement;
use App\Entity\MaitreIslamique;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Bundle\SecurityBundle\Security;

class EnseignementType extends AbstractType
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre de l\'enseignement',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Titre de la leçon ou du cours'
                ]
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Contenu de l\'enseignement',
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 10,
                    'placeholder' => 'Développez votre enseignement ici...'
                ]
            ])
            ->add('summary', TextareaType::class, [
                'label' => 'Résumé',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3,
                    'placeholder' => 'Résumé de l\'enseignement'
                ]
            ])
            ->add('category', ChoiceType::class, [
                'label' => 'Catégorie d\'enseignement',
                'choices' => [
                    'Fiqh (Jurisprudence)' => 'fiqh',
                    'Tafsir (Exégèse)' => 'tafsir',
                    'Hadith' => 'hadith',
                    'Aqida (Croyance)' => 'aqida',
                    'Sira (Biographie du Prophète)' => 'sira',
                    'Histoire islamique' => 'histoire',
                    'Langue arabe' => 'arabe',
                    'Récitation coranique' => 'recitation',
                    'Éthique et spiritualité' => 'ethique',
                    'Sciences islamiques' => 'sciences'
                ],
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('level', ChoiceType::class, [
                'label' => 'Niveau',
                'choices' => [
                    'Débutant' => 'beginner',
                    'Intermédiaire' => 'intermediate',
                    'Avancé' => 'advanced',
                    'Expert/Spécialisé' => 'expert'
                ],
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('duration', TextType::class, [
                'label' => 'Durée estimée',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: 45 minutes, 1 heure, 2 séances'
                ]
            ])
            ->add('supportFile', FileType::class, [
                'label' => 'Document de support (PDF)',
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'class' => 'form-control-file'
                ],
                'constraints' => [
                    new File([
                        'maxSize' => '10M',
                        'mimeTypes' => [
                            'application/pdf',
                            'application/msword',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger un document PDF ou Word',
                    ])
                ],
            ])
            ->add('audioFile', FileType::class, [
                'label' => 'Enregistrement audio (optionnel)',
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'class' => 'form-control-file'
                ],
                'constraints' => [
                    new File([
                        'maxSize' => '50M',
                        'mimeTypes' => [
                            'audio/mpeg',
                            'audio/mp4',
                            'audio/wav'
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger un fichier audio valide',
                    ])
                ],
            ])
            ->add('prerequisites', TextareaType::class, [
                'label' => 'Prérequis',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3,
                    'placeholder' => 'Connaissances nécessaires pour suivre cet enseignement'
                ]
            ])
            ->add('objectives', TextareaType::class, [
                'label' => 'Objectifs pédagogiques',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3,
                    'placeholder' => 'Que va apprendre l\'étudiant ?'
                ]
            ])
            ->add('references', TextareaType::class, [
                'label' => 'Références bibliographiques',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4,
                    'placeholder' => 'Sources et références utilisées'
                ]
            ])
            ->add('isPublished', CheckboxType::class, [
                'label' => 'Publier l\'enseignement',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input'
                ]
            ])
            ->add('isOpenAccess', CheckboxType::class, [
                'label' => 'Accès libre (sinon réservé aux utilisateurs connectés)',
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
                    'placeholder' => 'Mots-clés séparés par des virgules'
                ]
            ])
            ->add('language', ChoiceType::class, [
                'label' => 'Langue d\'enseignement',
                'choices' => [
                    'Français' => 'fr',
                    'Arabe' => 'ar',
                    'Anglais' => 'en',
                    'Bilingue Français-Arabe' => 'fr-ar'
                ],
                'attr' => [
                    'class' => 'form-control'
                ]
            ]);

        // Si l'utilisateur est admin, il peut choisir le maître, sinon c'est automatique
        if ($this->security->isGranted('ROLE_ADMIN')) {
            $builder->add('maitre', EntityType::class, [
                'label' => 'Maître islamique',
                'class' => MaitreIslamique::class,
                'choice_label' => function(MaitreIslamique $maitre) {
                    return $maitre->getUser() ? $maitre->getUser()->getFullName() : $maitre->getName();
                },
                'placeholder' => 'Sélectionnez un maître',
                'attr' => [
                    'class' => 'form-control'
                ]
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Enseignement::class,
        ]);
    }
}