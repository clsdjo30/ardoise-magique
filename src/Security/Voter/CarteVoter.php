<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Carte;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class CarteVoter extends Voter
{
    public const CREATE = 'CARTE_CREATE';
    public const VIEW = 'CARTE_VIEW';
    public const EDIT = 'CARTE_EDIT';
    public const DELETE = 'CARTE_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        // CREATE doesn't need a subject
        if ($attribute === self::CREATE) {
            return true;
        }

        // Other operations need a Carte entity
        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE])
            && $subject instanceof Carte;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // User must be authenticated
        if (!$user instanceof User) {
            return false;
        }

        // Super admin can do everything
        if (in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
            return true;
        }

        return match ($attribute) {
            self::CREATE => $this->canCreate($user),
            self::VIEW => $this->canView($subject, $user),
            self::EDIT => $this->canEdit($subject, $user),
            self::DELETE => $this->canDelete($subject, $user),
            default => false,
        };
    }

    private function canCreate(User $user): bool
    {
        // Only PREMIUM users can create cartes
        return $user->getSubscription()?->isPremium() ?? false;
    }

    private function canView(Carte $carte, User $user): bool
    {
        // User can view if they own the restaurant
        return $carte->getRestaurant()->getOwner() === $user;
    }

    private function canEdit(Carte $carte, User $user): bool
    {
        // Must be PREMIUM and owner
        return $this->canView($carte, $user)
            && ($user->getSubscription()?->isPremium() ?? false);
    }

    private function canDelete(Carte $carte, User $user): bool
    {
        // User can delete if they own the restaurant
        return $this->canView($carte, $user);
    }
}
