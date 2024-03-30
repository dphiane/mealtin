<?php

namespace App\Security\Voter;

use App\Entity\Reservation;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ReservationVoter extends Voter
{
    public const EDIT = 'ACCESS_EDIT_RESERVATION';
    public const VIEW = 'reservation_view';

    protected function supports(string $attribute, mixed $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::EDIT, self::VIEW])
            && $subject instanceof Reservation;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof User) {
            return false;
        }

        if (!$subject instanceof Reservation) {
            return false;
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::EDIT:
                return $subject->getUser() === $user;
                break;

            case self::VIEW:
                // logic to determine if the user can VIEW
                return $subject->getUser() === $user;
                break;
        }

        return false;
    }
}
