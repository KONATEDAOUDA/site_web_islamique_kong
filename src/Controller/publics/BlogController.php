<?php

namespace App\Controller\publics;

use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\Favoris;
use App\Form\CommentType;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/actualité')]
class BlogController extends AbstractController
{
    #[Route('/', name: 'app_blog_index')]
    public function index(ArticleRepository $articleRepository, CategoryRepository $categoryRepository): Response
    {
        return $this->render('frontend/article/index.html.twig', [
            'articles' => $articleRepository->findPublishedArticles(),
            'categories' => $categoryRepository->findAll(),
        ]);
    }

    #[Route('/{slug}', name: 'app_blog_show')]
    public function show(string $slug, Request $request,
                        ArticleRepository $articleRepository,
                        EntityManagerInterface $entityManager,
                        RequestStack $requestStack): Response
    {

        $article = $articleRepository->findOneBy(['slug' => $slug, 'isPublished' => true]);

        if (!$article) {
            throw $this->createNotFoundException('Cet article n\'existe pas ou n\'est pas publié');
        }

        $comment = new Comment();
        $form = null;

        if ($this->getUser()) {
            $comment->setAuthor($this->getUser());
            $comment->setArticle($article);
            $comment->setCreatedAt(new \DateTime());
            $comment->setIsApproved(false); // à modérer
            $comment->setIpAddress($request->getClientIp());
            $comment->setUserAgent($request->headers->get('User-Agent'));

            $form = $this->createForm(CommentType::class, $comment);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager->persist($comment);
                $entityManager->flush();

                $this->addFlash('success', 'Votre commentaire a été ajouté et sera visible après modération');

                return $this->redirectToRoute('app_blog_show', ['slug' => $article->getSlug()]);
            }
        }

        $approvedComments = $entityManager->getRepository(Comment::class)
            ->findBy(['article' => $article, 'isApproved' => true], ['createdAt' => 'DESC']);

        return $this->render('frontend/article/show_article.html.twig', [
            'article' => $article,
            'comments' => $approvedComments,
            'form' => $form ? $form->createView() : null,
        ]);
    }

    #[Route('/categorie/{slug}', name: 'app_blog_category')]
    public function showCategory(string $slug, ArticleRepository $articleRepository, CategoryRepository $categoryRepository): Response
    {
        $category = $categoryRepository->findOneBy(['slug' => $slug]);

        if (!$category) {
            throw $this->createNotFoundException('Cette catégorie n\'existe pas');
        }

        return $this->render('frontend/article/category_show.html.twig', [
            'category' => $category,
            'articles' => $articleRepository->findByCategory($category),
            'categories' => $categoryRepository->findAll(),
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
