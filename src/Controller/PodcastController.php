<?php

namespace App\Controller;

use App\Entity\Podcast;
use App\Repository\PodcastRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/podcasts')]
class PodcastController extends AbstractController
{
    #[Route('/', name: 'app_podcast_index')]
    public function index(PodcastRepository $podcastRepository): Response
    {
        $audioPodcasts = $podcastRepository->findBy(['type' => 'audio', 'isPublished' => true], ['createdAt' => 'DESC']);
        $videoPodcasts = $podcastRepository->findBy(['type' => 'video', 'isPublished' => true], ['createdAt' => 'DESC']);

        return $this->render('frontend/podcast/index.html.twig', [
            'audioPodcasts' => $audioPodcasts,
            'videoPodcasts' => $videoPodcasts,
        ]);
    }

    #[Route('/audio', name: 'app_podcast_audio')]
    public function audio(PodcastRepository $podcastRepository): Response
    {
        $podcasts = $podcastRepository->findBy(['type' => 'audio', 'isPublished' => true], ['createdAt' => 'DESC']);

        return $this->render('frontend/podcast/audio.html.twig', [
            'podcasts' => $podcasts,
        ]);
    }

    #[Route('/video', name: 'app_podcast_video')]
    public function video(PodcastRepository $podcastRepository): Response
    {
        $podcasts = $podcastRepository->findBy(['type' => 'video', 'isPublished' => true], ['createdAt' => 'DESC']);

        return $this->render('frontend/podcast/video.html.twig', [
            'podcasts' => $podcasts,
        ]);
    }

    #[Route('/{slug}', name: 'app_podcast_show')]
    public function show(string $slug, PodcastRepository $podcastRepository): Response
    {
        $podcast = $podcastRepository->findOneBy(['slug' => $slug, 'isPublished' => true]);

        if (!$podcast) {
            throw $this->createNotFoundException('Ce podcast n\'existe pas ou n\'est pas publié');
        }

        // Recommandations: podcasts similaires (même type)
        $similarPodcasts = $podcastRepository->findBy(
            ['type' => $podcast->getType(), 'isPublished' => true],
            ['createdAt' => 'DESC'],
            3
        );

        return $this->render('frontend/podcast/show.html.twig', [
            'podcast' => $podcast,
            'similarPodcasts' => $similarPodcasts,
        ]);
    }

    #[Route('/{id}/favorite', name: 'app_podcast_favorite', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function addToFavorites(Podcast $podcast, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if ($user->getFavoritePodcasts()->contains($podcast)) {
            $user->removeFavoritePodcast($podcast);
            $message = 'Le podcast a été retiré de vos favoris';
        } else {
            $user->addFavoritePodcast($podcast);
            $message = 'Le podcast a été ajouté à vos favoris';
        }

        $entityManager->flush();

        $this->addFlash('success', $message);

        return $this->redirectToRoute('app_podcast_show', ['slug' => $podcast->getSlug()]);
    }
}
