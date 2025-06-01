<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\Favoris;
use App\Form\CommentType;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/blog')]
class BlogController extends AbstractController
{
    #[Route('/', name: 'app_blog_index')]
    public function index(ArticleRepository $articleRepository, CategoryRepository $categoryRepository): Response
    {
        return $this->render('frontend/blog/index.html.twig', [
            'articles' => $articleRepository->findPublishedArticles(),
            'categories' => $categoryRepository->findAll(),
        ]);
    }

    #[Route('/category/{slug}', name: 'app_blog_category')]
    public function showCategory(string $slug, ArticleRepository $articleRepository, CategoryRepository $categoryRepository): Response
    {
        $category = $categoryRepository->findOneBy(['slug' => $slug]);

        if (!$category) {
            throw $this->createNotFoundException('Cette catégorie n\'existe pas');
        }

        return $this->render('frontend/blog/category.html.twig', [
            'category' => $category,
            'articles' => $articleRepository->findByCategory($category),
            'categories' => $categoryRepository->findAll(),
        ]);
    }

    #[Route('/{slug}', name: 'app_blog_show')]
    public function show(string $slug, Request $request, ArticleRepository $articleRepository, EntityManagerInterface $entityManager): Response
    {
        $article = $articleRepository->findOneBy(['slug' => $slug, 'isPublished' => true]);

        if (!$article) {
            throw $this->createNotFoundException('Cet article n\'existe pas ou n\'est pas publié');
        }

        // Création du formulaire de commentaire si l'utilisateur est connecté
        $comment = new Comment();
        $form = null;
        
        if ($this->getUser()) {
            $comment->setAuthor($this->getUser());
            $comment->setArticle($article);
            $form = $this->createForm(CommentType::class, $comment);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager->persist($comment);
                $entityManager->flush();

                $this->addFlash('success', 'Votre commentaire a été ajouté et sera visible après modération');

                return $this->redirectToRoute('app_blog_show', ['slug' => $article->getSlug()]);
            }
        }

        // Récupérer les commentaires approuvés
        $approvedComments = $entityManager->getRepository(Comment::class)
            ->findBy(['article' => $article, 'isApproved' => true], ['createdAt' => 'DESC']);

        return $this->render('frontend/blog/show.html.twig', [
            'article' => $article,
            'comments' => $approvedComments,
            'form' => $form ? $form->createView() : null,
        ]);
    }

    #[Route('/{id}/favorite', name: 'app_blog_favorite', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function addToFavorites(Article $article, EntityManagerInterface $entityManager): Response
    {
        $favoris = $entityManager->getRepository(Favoris::class)->findOneBy(['user' => $this->getUser(), 'article' => $article]);

        if ($favoris) {
            $entityManager->remove($favoris);
            $message = 'L\'article a été retiré de vos favoris';
        } else {
            $favoris = new Favoris();
            $favoris->setUser($this->getUser());
            $favoris->setArticle($article);
            $entityManager->persist($favoris);
            $message = 'L\'article a été ajouté à vos favoris';
        }

        $entityManager->flush();

        $this->addFlash('success', $message);

        return $this->redirectToRoute('app_blog_show', ['slug' => $article->getSlug()]);
    }
}
