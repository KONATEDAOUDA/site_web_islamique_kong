<?php

namespace App\Security;

use App\Entity\Enseignement;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class EnseignementVoter extends Voter implements VoterInterface
{
    public const EDIT = 'ENSEIGNEMENT_EDIT';
    public const VIEW = 'ENSEIGNEMENT_VIEW';
    public const DELETE = 'ENSEIGNEMENT_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::VIEW, self::DELETE])
            && $subject instanceof Enseignement;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        /** @var Enseignement $enseignement */
        $enseignement = $subject;

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($enseignement, $user);
            case self::EDIT:
                return $this->canEdit($enseignement, $user);
            case self::DELETE:
                return $this->canDelete($enseignement, $user);
        }

        return false;
    }

    private function canView(Enseignement $enseignement, User $user): bool
    {
        return in_array('ROLE_TEACHER', $user->getRoles()) 
            || in_array('ROLE_DAVE_SUPER_ADMIN_2108', $user->getRoles())
            || in_array('ROLE_SUPERVISOR', $user->getRoles())
            || $enseignement->getAuthor() === $user;
    }

    private function canEdit(Enseignement $enseignement, User $user): bool
    {
        return in_array('ROLE_TEACHER', $user->getRoles()) 
            || in_array('ROLE_DAVE_SUPER_ADMIN_2108', $user->getRoles())
            || in_array('ROLE_SUPERVISOR', $user->getRoles())
            || $enseignement->getAuthor() === $user;
    }

    private function canDelete(Enseignement $enseignement, User $user): bool
    {
        return in_array('ROLE_DAVE_SUPER_ADMIN_2108', $user->getRoles()) 
            || in_array('ROLE_SUPERVISOR', $user->getRoles())
            || (in_array('ROLE_TEACHER', $user->getRoles()) && $enseignement->getAuthor() === $user);
    }
}