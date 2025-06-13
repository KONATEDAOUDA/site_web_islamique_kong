<?php

namespace App\Controller\Admin;

use App\Entity\Podcast;
use App\Form\PodcastType;
use App\Repository\PodcastRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/dashboard/podcasts')]
class PodcastController extends AbstractController
{
    public function __construct(
        private PodcastRepository $podcastRepository,
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('/', name: 'admin_podcast_index')]
    #[IsGranted('ROLE_TEACHER')]
    public function index(): Response
    {
        $podcasts = $this->podcastRepository->findBy([], ['createdAt' => 'DESC']);
        
        return $this->render('admin/podcast/index.html.twig', [
            'podcasts' => $podcasts,
        ]);
    }

    #[Route('/new', name: 'admin_podcast_new')]
    #[IsGranted('ROLE_TEACHER')]
    public function new(Request $request, SluggerInterface $slugger): Response
    {
        $podcast = new Podcast();
        $form = $this->createForm(PodcastType::class, $podcast);
        $form->handleRequest($request);

        // DEBUGGING - à supprimer après résolution
        if ($request->isMethod('POST')) {
            dump('Form submitted');
            dump('Form valid?', $form->isValid());
            
            if (!$form->isValid()) {
                dump('Form errors:', $form->getErrors(true));
                foreach ($form->all() as $child) {
                    if ($child->getErrors()->count() > 0) {
                        dump($child->getName() . ' errors:', $child->getErrors());
                    }
                }
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Vérification des champs obligatoires
                if (!$podcast->getTitle()) {
                    throw new \Exception('Le titre est requis');
                }
                if (!$podcast->getType()) {
                    throw new \Exception('Le type est requis');
                }

                // Gestion de l'auteur
                $podcast->setAuthor($this->getUser());
                
                // Génération automatique du slug
                $podcast->computeSlug($slugger);

                // Vérifier qu'au moins un fichier ou une URL externe est fourni
                $audioFile = $form->get('audioFile')->getData();
                $externalUrl = $podcast->getExternalUrl();

                if (!$audioFile && !$externalUrl) {
                    $this->addFlash('error', 'Veuillez fournir soit un fichier audio/vidéo, soit une URL externe.');
                    return $this->render('admin/podcast/new.html.twig', [
                        'podcast' => $podcast,
                        'form' => $form,
                    ]);
                }

                // Gestion de l'upload de fichier audio/vidéo
                if ($audioFile) {
                    $this->handleAudioUpload($audioFile, $podcast, $slugger);
                }

                // Gestion de l'upload de l'image
                $thumbnailFile = $form->get('thumbnail')->getData();
                if ($thumbnailFile) {
                    $this->handleThumbnailUpload($thumbnailFile, $podcast, $slugger);
                }

                // Gestion de la date de publication
                if ($podcast->isIsPublished()) {
                    $podcast->setPublishedAt(new \DateTimeImmutable());
                }

                // DEBUG
                dump('Before persist:', $podcast);

                $this->entityManager->persist($podcast);
                $this->entityManager->flush();

                $this->addFlash('success', 'Podcast créé avec succès !');
                return $this->redirectToRoute('admin_podcast_index');

            } catch (\Exception $e) {
                dump('Exception caught:', $e->getMessage());
                $this->addFlash('error', 'Erreur lors de la création du podcast: ' . $e->getMessage());
            }
        }

        return $this->render('admin/podcast/new.html.twig', [
            'podcast' => $podcast,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_podcast_show', requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_TEACHER')]
    public function show(Podcast $podcast): Response
    {
        return $this->render('admin/podcast/show.html.twig', [
            'podcast' => $podcast,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_podcast_edit', requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_TEACHER')]
    public function edit(Request $request, Podcast $podcast, SluggerInterface $slugger): Response
    {
        // Vérifier que l'utilisateur peut modifier ce podcast
        if (!$this->isGranted('ROLE_DAVE_SUPER_ADMIN_2108') && $podcast->getAuthor() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(PodcastType::class, $podcast);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de l'upload de fichier audio/vidéo (si nouveau fichier)
            $audioFile = $form->get('audioFile')->getData();
            if ($audioFile) {
                $this->handleAudioUpload($audioFile, $podcast, $slugger);
            }

            // Gestion de l'upload de l'image (si nouvelle image)
            $thumbnailFile = $form->get('thumbnail')->getData();
            if ($thumbnailFile) {
                $this->handleThumbnailUpload($thumbnailFile, $podcast, $slugger);
            }

            // Gestion de la date de publication
            if ($podcast->isIsPublished() && !$podcast->getPublishedAt()) {
                $podcast->setPublishedAt(new \DateTimeImmutable());
            }

            $this->entityManager->flush();

            $this->addFlash('success', 'Podcast modifié avec succès !');
            return $this->redirectToRoute('admin_podcast_index');
        }

        return $this->render('admin/podcast/edit.html.twig', [
            'podcast' => $podcast,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_podcast_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsGranted('ROLE_TEACHER')]
    public function delete(Request $request, Podcast $podcast): Response
    {
        // Vérifier que l'utilisateur peut supprimer ce podcast
        if (!$this->isGranted('ROLE_DAVE_SUPER_ADMIN_2108') && $podcast->getAuthor() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete'.$podcast->getId(), $request->request->get('_token'))) {
            // Supprimer les fichiers associés
            $this->deleteAssociatedFiles($podcast);
            
            $this->entityManager->remove($podcast);
            $this->entityManager->flush();
            $this->addFlash('success', 'Podcast supprimé avec succès !');
        }

        return $this->redirectToRoute('admin_podcast_index');
    }

    #[Route('/{id}/feature', name: 'admin_podcast_feature', requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_TEACHER')]
    public function feature(Podcast $podcast): Response
    {
        $podcast->setIsFeatured(true);
        $this->entityManager->flush();

        $this->addFlash('success', 'Podcast mis à la une.');
        return $this->redirectToRoute('admin_podcast_show', ['id' => $podcast->getId()]);
    }

    /**
     * Gère l'upload du fichier audio/vidéo
     */
    private function handleAudioUpload($audioFile, Podcast $podcast, SluggerInterface $slugger): void
    {
        $originalFilename = pathinfo($audioFile->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $slugger->slug($originalFilename);
        $fileName = $safeFilename.'-'.uniqid().'.'.$audioFile->guessExtension();

        try {
            $uploadsDirectory = $this->getParameter('kernel.project_dir').'/public/uploads/podcasts';
            
            // Créer le dossier s'il n'existe pas
            if (!is_dir($uploadsDirectory)) {
                mkdir($uploadsDirectory, 0755, true);
            }

            $audioFile->move($uploadsDirectory, $fileName);
            
            // Sauvegarder le chemin du fichier
            $podcast->setFilePath($fileName);
            
            // Sauvegarder la taille du fichier
            $filePath = $uploadsDirectory.'/'.$fileName;
            if (file_exists($filePath)) {
                $podcast->setFileSize(filesize($filePath));
            }
            
            // Calculer la durée du fichier (optionnel, nécessite des extensions supplémentaires)
            $this->calculateMediaDuration($filePath, $podcast);
            
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de l\'upload du fichier audio/vidéo: ' . $e->getMessage());
        }
    }

    /**
     * Gère l'upload de l'image de couverture
     */
    private function handleThumbnailUpload($thumbnailFile, Podcast $podcast, SluggerInterface $slugger): void
    {
        $originalFilename = pathinfo($thumbnailFile->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $slugger->slug($originalFilename);
        $fileName = $safeFilename.'-'.uniqid().'.'.$thumbnailFile->guessExtension();

        try {
            $uploadsDirectory = $this->getParameter('kernel.project_dir').'/public/uploads/podcasts/thumbnails';
            
            // Créer le dossier s'il n'existe pas
            if (!is_dir($uploadsDirectory)) {
                mkdir($uploadsDirectory, 0755, true);
            }

            $thumbnailFile->move($uploadsDirectory, $fileName);
            
            // Sauvegarder le nom du fichier
            $podcast->setThumbnail($fileName);
            
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de l\'upload de l\'image: ' . $e->getMessage());
        }
    }

    /**
     * Calcule la durée du fichier média (version basique)
     */
    private function calculateMediaDuration(string $filePath, Podcast $podcast): void
    {
        // Version simplifiée - vous pouvez utiliser des librairies comme FFMpeg pour plus de précision
        try {
            // Pour l'instant, on utilise la durée saisie manuellement
            if ($podcast->getDuration()) {
                $podcast->setCalculatedDuration($podcast->getDuration());
            }
        } catch (\Exception $e) {
            // Ignorer les erreurs de calcul de durée
        }
    }

    /**
     * Supprime les fichiers associés au podcast
     */
    private function deleteAssociatedFiles(Podcast $podcast): void
    {
        $projectDir = $this->getParameter('kernel.project_dir');
        
        // Supprimer le fichier audio/vidéo
        if ($podcast->getFilePath()) {
            $audioPath = $projectDir . '/public/uploads/podcasts/' . $podcast->getFilePath();
            if (file_exists($audioPath)) {
                unlink($audioPath);
            }
        }
        
        // Supprimer l'image de couverture
        if ($podcast->getThumbnail()) {
            $thumbnailPath = $projectDir . '/public/uploads/podcasts/thumbnails/' . $podcast->getThumbnail();
            if (file_exists($thumbnailPath)) {
                unlink($thumbnailPath);
            }
        }
    }
   
    }