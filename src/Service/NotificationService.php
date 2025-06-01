<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class NotificationService
{
    private MailerInterface $mailer;
    private Environment $twig;

    public function __construct(MailerInterface $mailer, Environment $twig)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
    }

    public function sendEmail(string $to, string $subject, string $template, array $context = []): void
    {
        $htmlContent = $this->twig->render($template, $context);

        $email = (new Email())
            ->from('noreply@islamique-kong.ci')
            ->to($to)
            ->subject($subject)
            ->html($htmlContent);

        $this->mailer->send($email);
    }

    public function sendWelcomeEmail(string $userEmail, string $userName): void
    {
        $this->sendEmail(
            $userEmail,
            'Bienvenue sur le Portail Islamique de Kong',
            'emails/welcome.html.twig',
            ['userName' => $userName]
        );
    }

    public function sendPasswordResetEmail(string $userEmail, string $resetToken): void
    {
        $this->sendEmail(
            $userEmail,
            'Réinitialisation de votre mot de passe',
            'emails/password_reset.html.twig',
            ['resetToken' => $resetToken]
        );
    }

    public function sendContentPublishedNotification(string $adminEmail, string $contentType, string $contentTitle): void
    {
        $this->sendEmail(
            $adminEmail,
            'Nouveau contenu publié',
            'emails/content_published.html.twig',
            [
                'contentType' => $contentType,
                'contentTitle' => $contentTitle
            ]
        );
    }

    public function sendBulkNotification(array $recipients, string $subject, string $template, array $context = []): void
    {
        foreach ($recipients as $recipient) {
            try {
                $this->sendEmail($recipient, $subject, $template, $context);
            } catch (\Exception $e) {
                // Log l'erreur mais continue avec les autres destinataires
                error_log("Failed to send email to {$recipient}: " . $e->getMessage());
            }
        }
    }

    public function sendAdminAlert(string $message, string $level = 'info'): void
    {
        $adminEmail = 'admin@islamique-kong.ci'; // Vous pouvez mettre ça en paramètre
        
        $this->sendEmail(
            $adminEmail,
            'Alerte Système - Portail Islamique Kong',
            'emails/admin_alert.html.twig',
            [
                'message' => $message,
                'level' => $level,
                'timestamp' => new \DateTime()
            ]
        );
    }

    public function sendNewsletterEmail(array $subscribers, string $subject, string $content): void
    {
        $this->sendBulkNotification(
            $subscribers,
            $subject,
            'emails/newsletter.html.twig',
            ['content' => $content]
        );
    }

    public function sendContactFormNotification(string $senderName, string $senderEmail, string $message): void
    {
        $adminEmail = 'contact@islamique-kong.ci';
        
        $this->sendEmail(
            $adminEmail,
            'Nouveau message de contact',
            'emails/contact_form.html.twig',
            [
                'senderName' => $senderName,
                'senderEmail' => $senderEmail,
                'message' => $message
            ]
        );
    }
}
