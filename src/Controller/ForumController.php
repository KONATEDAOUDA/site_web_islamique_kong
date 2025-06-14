<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class ForumController extends AbstractController
{
    public function index(): Response
    {
        return $this->render('frontend/forum/index.html.twig', [
            'pageTitle' => 'Forum Communautaire',
            'metaDescription' => 'Rejoignez notre communauté pour échanger sur l\'Islam, l\'histoire de Kong et partager vos connaissances avec les érudits et membres.',
        ]);
    }

    public function subscribe(Request $request): JsonResponse
    {
        if (!$request->isMethod('POST')) {
            return new JsonResponse(['success' => false, 'message' => 'Méthode non autorisée'], 405);
        }

        $email = $request->request->get('email');
        $acceptUpdates = $request->request->get('forum_updates') === 'on';

        // Validation simple
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return new JsonResponse([
                'success' => false, 
                'message' => 'Adresse email invalide'
            ], 400);
        }

        if (!$acceptUpdates) {
            return new JsonResponse([
                'success' => false, 
                'message' => 'Vous devez accepter de recevoir les notifications'
            ], 400);
        }

        // TODO: Ici vous pouvez ajouter la logique pour sauvegarder l'email
        // Par exemple : enregistrer en base de données, envoyer à un service email, etc.
        
        // Pour l'instant, on simule une inscription réussie
        try {
            // Simulation de sauvegarde
            // $this->saveForumSubscription($email);
            
            return new JsonResponse([
                'success' => true,
                'message' => 'Merci ! Vous serez notifié lors du lancement du forum.'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Une erreur est survenue. Veuillez réessayer.'
            ], 500);
        }
    }

    /**
     * Méthode pour sauvegarder la souscription (à implémenter plus tard)
     */
    private function saveForumSubscription(string $email): void
    {
        // TODO: Implémenter la sauvegarde en base de données
        // Exemple :
        // $subscription = new ForumSubscription();
        // $subscription->setEmail($email);
        // $subscription->setCreatedAt(new \DateTime());
        // $entityManager->persist($subscription);
        // $entityManager->flush();
    }
}