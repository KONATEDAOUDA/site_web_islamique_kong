<?php

namespace App\Security;

use App\Entity\Podcast;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class PodcastVoter extends Voter implements VoterInterface
{
    public const EDIT = 'PODCAST_EDIT';
    public const VIEW = 'PODCAST_VIEW';
    public const DELETE = 'PODCAST_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::VIEW, self::DELETE])
            && $subject instanceof Podcast;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        /** @var Podcast $podcast */
        $podcast = $subject;

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($podcast, $user);
            case self::EDIT:
                return $this->canEdit($podcast, $user);
            case self::DELETE:
                return $this->canDelete($podcast, $user);
        }

        return false;
    }

    private function canView(Podcast $podcast, User $user): bool
    {
        return in_array('ROLE_PODCAST_MANAGER', $user->getRoles()) 
            || in_array('ROLE_SUPERVISOR', $user->getRoles())
            || in_array('ROLE_DAVE_SUPER_ADMIN_2108', $user->getRoles())
            || $podcast->getAuthor() === $user;
    }

    private function canEdit(Podcast $podcast, User $user): bool
    {
        return in_array('ROLE_PODCAST_MANAGER', $user->getRoles()) 
            || in_array('ROLE_SUPERVISOR', $user->getRoles())
            || in_array('ROLE_DAVE_SUPER_ADMIN_2108', $user->getRoles())
            || $podcast->getAuthor() === $user;
    }

    private function canDelete(Podcast $podcast, User $user): bool
    {
        return in_array('ROLE_DAVE_SUPER_ADMIN_2108', $user->getRoles())
            || (in_array('ROLE_PODCAST_MANAGER', $user->getRoles()) && $podcast->getAuthor() === $user)
            || (in_array('ROLE_SUPERVISOR', $user->getRoles()) && $podcast->getAuthor() === $user);
    }
}