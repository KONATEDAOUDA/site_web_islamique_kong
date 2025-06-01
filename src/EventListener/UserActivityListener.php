<?php

namespace App\EventListener;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Bundle\SecurityBundle\Security;
use Psr\Log\LoggerInterface;

class UserActivityListener implements EventSubscriberInterface
{
    private EntityManagerInterface $entityManager;
    private Security $security;
    private LoggerInterface $logger;

    public function __construct(
        EntityManagerInterface $entityManager,
        Security $security,
        LoggerInterface $logger
    ) {
        $this->entityManager = $entityManager;
        $this->security = $security;
        $this->logger = $logger;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'security.interactive_login' => 'onSecurityInteractiveLogin',
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }

    /**
     * Appelé lors de la connexion d'un utilisateur
     */
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event): void
    {
        $user = $event->getAuthenticationToken()->getUser();
        
        if (!$user instanceof User) {
            return;
        }

        try {
            // Mettre à jour la dernière connexion
            $user->setLastLoginAt(new \DateTimeImmutable());
            
            // Incrémenter le compteur de connexions
            if (method_exists($user, 'incrementLoginCount')) {
                $user->incrementLoginCount();
            }
            
            // Enregistrer l'IP de connexion
            $request = $event->getRequest();
            if (method_exists($user, 'setLastLoginIp')) {
                $user->setLastLoginIp($request->getClientIp());
            }
            
            // Enregistrer le user agent
            if (method_exists($user, 'setLastLoginUserAgent')) {
                $user->setLastLoginUserAgent($request->headers->get('User-Agent'));
            }

            $this->entityManager->flush();

            // Log de la connexion
            $this->logger->info('User login', [
                'user_id' => $user->getId(),
                'email' => $user->getEmail(),
                'ip' => $request->getClientIp(),
                'user_agent' => $request->headers->get('User-Agent'),
            ]);

            // Vérifications de sécurité
            $this->performSecurityChecks($user, $request);

        } catch (\Exception $e) {
            $this->logger->error('Error updating user login data', [
                'user_id' => $user->getId(),
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Appelé à chaque requête pour mettre à jour l'activité
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return;
        }

        // Mettre à jour l'activité toutes les 5 minutes maximum
        $lastActivity = $user->getLastActivityAt();
        $now = new \DateTimeImmutable();
        
        if (!$lastActivity || $now->getTimestamp() - $lastActivity->getTimestamp() > 300) {
            try {
                $user->setLastActivityAt($now);
                $this->entityManager->flush();
            } catch (\Exception $e) {
                // Ne pas faire échouer la requête pour une erreur d'activité
                $this->logger->warning('Failed to update user activity', [
                    'user_id' => $user->getId(),
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Effectue des vérifications de sécurité lors de la connexion
     */
    private function performSecurityChecks(User $user, $request): void
    {
        // Vérifier les connexions suspectes
        $this->checkSuspiciousLogin($user, $request);
        
        // Vérifier si l'utilisateur doit changer son mot de passe
        $this->checkPasswordExpiry($user);
        
        // Vérifier les tentatives de connexion multiples
        $this->checkMultipleLoginAttempts($user, $request);
    }

    /**
     * Vérifie les connexions suspectes
     */
    private function checkSuspiciousLogin(User $user, $request): void
    {
        $currentIp = $request->getClientIp();
        $lastIp = method_exists($user, 'getLastLoginIp') ? $user->getLastLoginIp() : null;
        
        // Si l'IP change et que ce n'est pas la première connexion
        if ($lastIp && $lastIp !== $currentIp) {
            $this->logger->warning('User login from different IP', [
                'user_id' => $user->getId(),
                'email' => $user->getEmail(),
                'previous_ip' => $lastIp,
                'current_ip' => $currentIp,
            ]);
            
            // Optionnel : envoyer un email de notification
            // $this->notificationService->sendSecurityAlert($user, 'ip_change');
        }

        // Vérifier le user agent
        $currentUserAgent = $request->headers->get('User-Agent');
        $lastUserAgent = method_exists($user, 'getLastLoginUserAgent') ? $user->getLastLoginUserAgent() : null;
        
        if ($lastUserAgent && $lastUserAgent !== $currentUserAgent) {
            $this->logger->info('User login from different browser/device', [
                'user_id' => $user->getId(),
                'email' => $user->getEmail(),
                'previous_ua' => substr($lastUserAgent, 0, 100),
                'current_ua' => substr($currentUserAgent, 0, 100),
            ]);
        }
    }

    /**
     * Vérifie l'expiration du mot de passe
     */
    private function checkPasswordExpiry(User $user): void
    {
        if (!method_exists($user, 'getPasswordChangedAt')) {
            return;
        }

        $passwordChangedAt = $user->getPasswordChangedAt();
        if (!$passwordChangedAt) {
            return;
        }

        $now = new \DateTimeImmutable();
        $daysSinceChange = $now->diff($passwordChangedAt)->days;
        
        // Mot de passe expire après 90 jours pour les admins, 180 pour les autres
        $expiryDays = in_array('ROLE_ADMIN', $user->getRoles()) ? 90 : 180;
        
        if ($daysSinceChange >= $expiryDays) {
            $this->logger->warning('User password expired', [
                'user_id' => $user->getId(),
                'email' => $user->getEmail(),
                'days_since_change' => $daysSinceChange,
            ]);
            
            // Marquer que l'utilisateur doit changer son mot de passe
            if (method_exists($user, 'setMustChangePassword')) {
                $user->setMustChangePassword(true);
            }
        } elseif ($daysSinceChange >= ($expiryDays - 7)) {
            // Avertissement 7 jours avant expiration
            $this->logger->info('User password expiring soon', [
                'user_id' => $user->getId(),
                'email' => $user->getEmail(),
                'days_remaining' => $expiryDays - $daysSinceChange,
            ]);
        }
    }

    /**
     * Vérifie les tentatives de connexion multiples
     */
    private function checkMultipleLoginAttempts(User $user, $request): void
    {
        $ip = $request->getClientIp();
        
        // Compter les connexions récentes depuis cette IP
        $recentLogins = $this->countRecentLogins($ip);
        
        if ($recentLogins > 10) { // Plus de 10 connexions en 1 heure
            $this->logger->warning('Multiple login attempts from same IP', [
                'ip' => $ip,
                'count' => $recentLogins,
                'user_id' => $user->getId(),
            ]);
        }
    }

    /**
     * Compte les connexions récentes depuis une IP
     */
    private function countRecentLogins(string $ip): int
    {
        $oneHourAgo = new \DateTimeImmutable('-1 hour');
        
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('COUNT(u.id)')
           ->from(User::class, 'u')
           ->where('u.lastLoginIp = :ip')
           ->andWhere('u.lastLoginAt >= :since')
           ->setParameter('ip', $ip)
           ->setParameter('since', $oneHourAgo);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Enregistre une action utilisateur spécifique
     */
    public function logUserAction(User $user, string $action, array $context = []): void
    {
        $this->logger->info('User action', array_merge([
            'user_id' => $user->getId(),
            'email' => $user->getEmail(),
            'action' => $action,
        ], $context));
    }

    /**
     * Nettoie les anciennes données d'activité
     */
    public function cleanupOldActivityData(): void
    {
        try {
            // Supprimer les logs d'activité de plus de 6 mois
            $sixMonthsAgo = new \DateTimeImmutable('-6 months');
            
            $qb = $this->entityManager->createQueryBuilder();
            $qb->update(User::class, 'u')
               ->set('u.lastActivityAt', ':null')
               ->where('u.lastActivityAt < :date')
               ->setParameter('date', $sixMonthsAgo)
               ->setParameter('null', null);

            $affected = $qb->getQuery()->execute();
            
            $this->logger->info('Cleaned up old user activity data', [
                'affected_users' => $affected,
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to cleanup old activity data', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
