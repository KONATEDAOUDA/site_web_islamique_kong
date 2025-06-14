<?php

namespace App\Controller\Admin;

use App\Entity\Article;
use App\Entity\Category;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface; 
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/dashboard/articles')]
#[IsGranted('ROLE_BLOG_MANAGER')]
class ArticleController extends AbstractController
{
    public function __construct(
        private ArticleRepository $articleRepository,
        private CategoryRepository $categoryRepository,
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('/', name: 'admin_article_index')]
    public function index(): Response
    {
        $articles = $this->articleRepository->findBy([], ['createdAt' => 'DESC']);
        
        return $this->render('admin/article/index.html.twig', [
            'articles' => $articles,
        ]);
    }

    #[Route('/new', name: 'admin_article_new')]
    public function new(Request $request, SluggerInterface $slugger): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de l'auteur et des champs de création
            $article->setAuthor($this->getUser());
            $article->setCreatedByUser($this->getUser()); // Nouveau champ

            // Génération automatique du slug
            $article->computeSlug($slugger);

            // Gestion de l'upload d'image
            $imageFile = $form->get('featuredImage')->getData();
            if ($imageFile) {
                $this->handleImageUpload($imageFile, $article, $slugger);
            }

            // Calcul du temps de lecture
            $this->calculateReadingTime($article);

            // Gestion de la date de publication
            if ($article->isIsPublished()) {
                $article->setPublishedAt(new \DateTimeImmutable());
            }

            $this->entityManager->persist($article);
            $this->entityManager->flush();

            $this->addFlash('success', 'Article créé avec succès !');
            return $this->redirectToRoute('admin_article_index');
        }

        return $this->render('admin/article/new.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_article_edit', requirements: ['id' => '\d+'])]
    public function edit(Request $request, Article $article, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de l'utilisateur qui modifie
            $article->setModifiedByUser($this->getUser()); // Nouveau champ

            // Gestion de l'upload d'image (si une nouvelle image est uploadée)
            $imageFile = $form->get('featuredImage')->getData();
            if ($imageFile) {
                $this->handleImageUpload($imageFile, $article, $slugger);
            }

            // Recalcul du temps de lecture
            $this->calculateReadingTime($article);

            // Gestion de la date de publication
            if ($article->isIsPublished() && !$article->getPublishedAt()) {
                $article->setPublishedAt(new \DateTimeImmutable());
            }

            $this->entityManager->flush();

            $this->addFlash('success', 'Article modifié avec succès !');
            return $this->redirectToRoute('admin_article_index');
        }

        return $this->render('admin/article/edit.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_article_show', requirements: ['id' => '\d+'])]
    public function show(Article $article): Response
    {
        return $this->render('admin/article/show.html.twig', [
            'article' => $article,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_article_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Request $request, Article $article): Response
    {
        if ($this->isCsrfTokenValid('delete'.$article->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($article);
            $this->entityManager->flush();
            $this->addFlash('success', 'Article supprimé avec succès !');
        }

        return $this->redirectToRoute('admin_article_index');
    }

    #[Route('/{id}/feature', name: 'admin_article_feature', requirements: ['id' => '\d+'])]
    public function feature(Article $article, EntityManagerInterface $em): Response
    {
        $article->setIsFeatured(true);
        $em->flush();

        $this->addFlash('success', 'Article mis à la une.');
        return $this->redirectToRoute('admin_article_show', ['id' => $article->getId()]);
    }

    /**
     * Gère l'upload et le traitement de l'image
     */
    private function handleImageUpload($imageFile, Article $article, SluggerInterface $slugger): void
    {
        $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $slugger->slug($originalFilename);
        $fileName = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

        try {
            $uploadsDirectory = $this->getParameter('kernel.project_dir').'/public/assets/uploads/articles';
            
            // Créer le dossier s'il n'existe pas
            if (!is_dir($uploadsDirectory)) {
                mkdir($uploadsDirectory, 0755, true);
            }

            $imageFile->move($uploadsDirectory, $fileName);
            
            // Sauvegarder le nom du fichier
            $article->setThumbnail($fileName);
            
            // Générer une miniature (optionnel)
            $this->generateThumbnail($fileName, $article);
            
        } catch (FileException $e) {
            $this->addFlash('error', 'Erreur lors de l\'upload de l\'image.');
        }
    }

    /**
     * Génère une miniature pour l'image
     */
    private function generateThumbnail(string $fileName, Article $article): void
    {
        // Vous pouvez utiliser une librairie comme Intervention Image
        // Pour l'instant, on utilise la même image
        $article->setFeaturedImageThumbnail($fileName);
    }

    /**
     * Calcule le temps de lecture basé sur le contenu
     */
    private function calculateReadingTime(Article $article): void
    {
        $content = strip_tags($article->getContent());
        $wordCount = str_word_count($content);
        
        // Moyenne de 200 mots par minute
        $readingTime = ceil($wordCount / 200);
        
        $article->setReadingTime($readingTime);
    }

    /**
     * Affiche un article publié sur le frontend.
     */
    #[Route('/{slug}', name: 'app_article_show')]
    public function show_article(string $slug, ArticleRepository $articleRepository): Response
    {
        $article = $articleRepository->findOneBy([
            'slug' => $slug,
            'isPublished' => true
        ]);
        
        if (!$article) {
            throw $this->createNotFoundException('Article non trouvé.');
        }

        // Incrémenter le compteur de vues
        $article->incrementViewCount();
        $this->entityManager->flush();

        return $this->render('frontend/article/show_article.html.twig', [
            'article' => $article,
        ]);
    }

    #[Route('/categories/{slug}', name: 'category_show')]
    public function category_show(string $slug, CategoryRepository $categoryRepository): Response
    {
        $category = $categoryRepository->findOneBy([
            'slug' => $slug
        ]);
        
        if (!$category) {
            throw $this->createNotFoundException('Catégorie non trouvée.');
        }

        return $this->render('frontend/article/category_show.html.twig', [
            'category' => $category,
        ]);
    }
}
