<?php

namespace App\Security;

use App\Entity\Archive;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ArchiveVoter extends Voter implements VoterInterface
{
    public const EDIT = 'ARCHIVE_EDIT';
    public const VIEW = 'ARCHIVE_VIEW';
    public const DELETE = 'ARCHIVE_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::VIEW, self::DELETE])
            && $subject instanceof Archive;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        /** @var Archive $archive */
        $archive = $subject;

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($archive, $user);
            case self::EDIT:
                return $this->canEdit($archive, $user);
            case self::DELETE:
                return $this->canDelete($archive, $user);
        }

        return false;
    }

    private function canView(Archive $archive, User $user): bool
    {
        return in_array('ROLE_ARCHIVE_MANAGER', $user->getRoles()) 
            || in_array('ROLE_ADMIN', $user->getRoles())
            || $archive->getAuthor() === $user;
    }

    private function canEdit(Archive $archive, User $user): bool
    {
        return in_array('ROLE_ARCHIVE_MANAGER', $user->getRoles()) 
            || in_array('ROLE_ADMIN', $user->getRoles())
            || $archive->getAuthor() === $user;
    }

    private function canDelete(Archive $archive, User $user): bool
    {
        return in_array('ROLE_ADMIN', $user->getRoles()) 
            || (in_array('ROLE_ARCHIVE_MANAGER', $user->getRoles()) && $archive->getAuthor() === $user);
    }
}