<?php

namespace App\Controller\Admin;

use App\Entity\Archive;
use App\Form\ArchiveType;
use App\Repository\ArchiveRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/dashboard/archives')]
#[IsGranted('ROLE_ARCHIVE_MANAGER')]
class ArchiveController extends AbstractController
{
    public function __construct(
        private ArchiveRepository $archiveRepository,
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('/', name: 'admin_archive_index')]
    public function index(): Response
    {
        $archives = $this->archiveRepository->findBy([], ['createdAt' => 'DESC']);
        
        return $this->render('admin/archive/index.html.twig', [
            'archives' => $archives,
        ]);
    }

    #[Route('/new', name: 'admin_archive_new')]
    public function new(Request $request): Response
    {
        $archive = new Archive();
        $form = $this->createForm(ArchiveType::class, $archive);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $archive->setCreatedAt(new \DateTime());
            
            $this->entityManager->persist($archive);
            $this->entityManager->flush();

            $this->addFlash('success', 'Archive créée avec succès !');
            return $this->redirectToRoute('admin_archive_index');
        }

        return $this->render('admin/archive/new.html.twig', [
            'archive' => $archive,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_archive_show', requirements: ['id' => '\d+'])]
    public function show(Archive $archive): Response
    {
        return $this->render('admin/archive/show.html.twig', [
            'archive' => $archive,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_archive_edit', requirements: ['id' => '\d+'])]
    public function edit(Request $request, Archive $archive): Response
    {
        $form = $this->createForm(ArchiveType::class, $archive);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $archive->setUpdatedAt(new \DateTime());
            $this->entityManager->flush();

            $this->addFlash('success', 'Archive modifiée avec succès !');
            return $this->redirectToRoute('admin_archive_index');
        }

        return $this->render('admin/archive/edit.html.twig', [
            'archive' => $archive,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_archive_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Request $request, Archive $archive): Response
    {
        if ($this->isCsrfTokenValid('delete'.$archive->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($archive);
            $this->entityManager->flush();
            $this->addFlash('success', 'Archive supprimée avec succès !');
        }

        return $this->redirectToRoute('admin_archive_index');
    }
}
