<?php

namespace App\Controller\Admin;

use App\Entity\ForumTopic;
use App\Entity\ForumPost;
use App\Repository\ForumTopicRepository;
use App\Repository\ForumPostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/dashboard/forum')]
#[IsGranted('ROLE_SUPERVISOR')]
class ForumController extends AbstractController
{
    public function __construct(
        private ForumTopicRepository $topicRepository,
        private ForumPostRepository $postRepository,
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('/', name: 'admin_forum_index')]
    public function index(): Response
    {
        $topics = $this->topicRepository->findBy([], ['createdAt' => 'DESC']);
        $latestPosts = $this->postRepository->findBy([], ['createdAt' => 'DESC'], 10);
        
        return $this->render('admin/forum/index.html.twig', [
            'topics' => $topics,
            'latestPosts' => $latestPosts,
        ]);
    }

    #[Route('/topics', name: 'admin_forum_topics')]
    public function topics(): Response
    {
        $topics = $this->topicRepository->findBy([], ['createdAt' => 'DESC']);
        
        return $this->render('admin/forum/topics.html.twig', [
            'topics' => $topics,
        ]);
    }

    #[Route('/topics/{id}', name: 'admin_forum_topic_show', requirements: ['id' => '\d+'])]
    public function showTopic(ForumTopic $topic): Response
    {
        $posts = $this->postRepository->findBy(['topic' => $topic], ['createdAt' => 'ASC']);
        
        return $this->render('admin/forum/topic_show.html.twig', [
            'topic' => $topic,
            'posts' => $posts,
        ]);
    }

    #[Route('/topics/{id}/toggle-status', name: 'admin_forum_topic_toggle_status', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function toggleTopicStatus(Request $request, ForumTopic $topic): Response
    {
        if ($this->isCsrfTokenValid('toggle-status'.$topic->getId(), $request->request->get('_token'))) {
            $topic->setIsActive(!$topic->isIsActive());
            $this->entityManager->flush();
            
            $status = $topic->isIsActive() ? 'activé' : 'désactivé';
            $this->addFlash('success', "Le sujet a été {$status} avec succès !");
        }

        return $this->redirectToRoute('admin_forum_topics');
    }

    #[Route('/topics/{id}/delete', name: 'admin_forum_topic_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function deleteTopic(Request $request, ForumTopic $topic): Response
    {
        if ($this->isCsrfTokenValid('delete'.$topic->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($topic);
            $this->entityManager->flush();
            $this->addFlash('success', 'Sujet supprimé avec succès !');
        }

        return $this->redirectToRoute('admin_forum_topics');
    }

    #[Route('/posts', name: 'admin_forum_posts')]
    public function posts(): Response
    {
        $posts = $this->postRepository->findBy([], ['createdAt' => 'DESC']);
        
        return $this->render('admin/forum/posts.html.twig', [
            'posts' => $posts,
        ]);
    }

    #[Route('/posts/{id}', name: 'admin_forum_post_show', requirements: ['id' => '\d+'])]
    public function showPost(ForumPost $post): Response
    {
        return $this->render('admin/forum/post_show.html.twig', [
            'post' => $post,
        ]);
    }

    #[Route('/posts/{id}/toggle-status', name: 'admin_forum_post_toggle_status', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function togglePostStatus(Request $request, ForumPost $post): Response
    {
        if ($this->isCsrfTokenValid('toggle-status'.$post->getId(), $request->request->get('_token'))) {
            $post->setIsActive(!$post->isIsActive());
            $this->entityManager->flush();
            
            $status = $post->isIsActive() ? 'activé' : 'désactivé';
            $this->addFlash('success', "Le message a été {$status} avec succès !");
        }

        return $this->redirectToRoute('admin_forum_posts');
    }

    #[Route('/posts/{id}/delete', name: 'admin_forum_post_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function deletePost(Request $request, ForumPost $post): Response
    {
        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($post);
            $this->entityManager->flush();
            $this->addFlash('success', 'Message supprimé avec succès !');
        }

        return $this->redirectToRoute('admin_forum_posts');
    }
}
