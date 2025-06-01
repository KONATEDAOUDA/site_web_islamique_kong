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
use Symfony\Component\Security\Http\Attribute\IsGranted;

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
    public function new(Request $request): Response
    {
        $podcast = new Podcast();
        $form = $this->createForm(PodcastType::class, $podcast);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $podcast->setAuthor($this->getUser());
            $podcast->setCreatedAt(new \DateTimeImmutable());
            
            $this->entityManager->persist($podcast);
            $this->entityManager->flush();

            $this->addFlash('success', 'Podcast créé avec succès !');
            return $this->redirectToRoute('admin_podcast_index');
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
    public function edit(Request $request, Podcast $podcast): Response
    {
        // Vérifier que l'utilisateur peut modifier ce podcast
        if (!$this->isGranted('ROLE_ADMIN') && $podcast->getAuthor() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(PodcastType::class, $podcast);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $podcast->setUpdatedAt(new \DateTimeImmutable());
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
        if (!$this->isGranted('ROLE_ADMIN') && $podcast->getAuthor() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete'.$podcast->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($podcast);
            $this->entityManager->flush();
            $this->addFlash('success', 'Podcast supprimé avec succès !');
        }

        return $this->redirectToRoute('admin_podcast_index');
    }
}
