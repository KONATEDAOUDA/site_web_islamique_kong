<?php

namespace App\Security;

use App\Entity\Article;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ArticleVoter extends Voter implements VoterInterface
{
    public const EDIT = 'ARTICLE_EDIT';
    public const VIEW = 'ARTICLE_VIEW';
    public const DELETE = 'ARTICLE_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::VIEW, self::DELETE])
            && $subject instanceof Article;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        /** @var Article $article */
        $article = $subject;

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($article, $user);
            case self::EDIT:
                return $this->canEdit($article, $user);
            case self::DELETE:
                return $this->canDelete($article, $user);
        }

        return false;
    }

    private function canView(Article $article, User $user): bool
    {
        return in_array('ROLE_BLOG_MANAGER', $user->getRoles()) 
            || in_array('ROLE_ADMIN', $user->getRoles())
            || $article->getAuthor() === $user;
    }

    private function canEdit(Article $article, User $user): bool
    {
        return in_array('ROLE_BLOG_MANAGER', $user->getRoles()) 
            || in_array('ROLE_ADMIN', $user->getRoles())
            || $article->getAuthor() === $user;
    }

    private function canDelete(Article $article, User $user): bool
    {
        return in_array('ROLE_ADMIN', $user->getRoles()) 
            || (in_array('ROLE_BLOG_MANAGER', $user->getRoles()) && $article->getAuthor() === $user);
    }
}
