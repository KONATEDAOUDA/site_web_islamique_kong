<?php

namespace App\Controller\Admin;

use App\Entity\Enseignement;
use App\Form\EnseignementType;
use App\Repository\EnseignementRepository;
use App\Repository\MaitreIslamiqueRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/dashboard/enseignements')]
#[IsGranted('ROLE_TEACHER')]
class EnseignementController extends AbstractController
{
    public function __construct(
        private EnseignementRepository $enseignementRepository,
        private MaitreIslamiqueRepository $maitreRepository,
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('/', name: 'admin_enseignement_index')]
    public function index(): Response
    {
        // Si l'utilisateur n'est pas admin, ne montrer que ses propres enseignements
        if ($this->isGranted('ROLE_ADMIN')) {
            $enseignements = $this->enseignementRepository->findBy([], ['createdAt' => 'DESC']);
        } else {
            // Récupérer le maître islamique associé à l'utilisateur
            $maitre = $this->maitreRepository->findOneBy(['user' => $this->getUser()]);
            if ($maitre) {
                $enseignements = $this->enseignementRepository->findBy(['maitre' => $maitre], ['createdAt' => 'DESC']);
            } else {
                $enseignements = [];
            }
        }
        
        return $this->render('admin/enseignement/index.html.twig', [
            'enseignements' => $enseignements,
        ]);
    }

    #[Route('/new', name: 'admin_enseignement_new')]
    public function new(Request $request): Response
    {
        $enseignement = new Enseignement();
        
        // Si l'utilisateur n'est pas admin, associer automatiquement son profil de maître
        if (!$this->isGranted('ROLE_ADMIN')) {
            $maitre = $this->maitreRepository->findOneBy(['user' => $this->getUser()]);
            if ($maitre) {
                $enseignement->setMaitre($maitre);
            }
        }
        
        $form = $this->createForm(EnseignementType::class, $enseignement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $enseignement->setCreatedAt(new \DateTimeImmutable());
            
            $this->entityManager->persist($enseignement);
            $this->entityManager->flush();

            $this->addFlash('success', 'Enseignement créé avec succès !');
            return $this->redirectToRoute('admin_enseignement_index');
        }

        return $this->render('admin/enseignement/new.html.twig', [
            'enseignement' => $enseignement,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_enseignement_show', requirements: ['id' => '\d+'])]
    public function show(Enseignement $enseignement): Response
    {
        // Vérifier les droits d'accès
        if (!$this->isGranted('ROLE_ADMIN') && 
            (!$enseignement->getMaitre() || $enseignement->getMaitre()->getUser() !== $this->getUser())) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('admin/enseignement/show.html.twig', [
            'enseignement' => $enseignement,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_enseignement_edit', requirements: ['id' => '\d+'])]
    public function edit(Request $request, Enseignement $enseignement): Response
    {
        // Vérifier les droits de modification
        if (!$this->isGranted('ROLE_ADMIN') && 
            (!$enseignement->getMaitre() || $enseignement->getMaitre()->getUser() !== $this->getUser())) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(EnseignementType::class, $enseignement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $enseignement->setUpdatedAt(new \DateTimeImmutable());
            $this->entityManager->flush();

            $this->addFlash('success', 'Enseignement modifié avec succès !');
            return $this->redirectToRoute('admin_enseignement_index');
        }

        return $this->render('admin/enseignement/edit.html.twig', [
            'enseignement' => $enseignement,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_enseignement_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Request $request, Enseignement $enseignement): Response
    {
        // Vérifier les droits de suppression
        if (!$this->isGranted('ROLE_ADMIN') && 
            (!$enseignement->getMaitre() || $enseignement->getMaitre()->getUser() !== $this->getUser())) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete'.$enseignement->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($enseignement);
            $this->entityManager->flush();
            $this->addFlash('success', 'Enseignement supprimé avec succès !');
        }

        return $this->redirectToRoute('admin_enseignement_index');
    }
}
