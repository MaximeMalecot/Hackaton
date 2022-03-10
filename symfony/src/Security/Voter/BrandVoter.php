<?php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class BrandVoter extends Voter
{
    const GENERATE = 'generate';

    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, [self::GENERATE]);
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        /** @var User $user */
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case self::GENERATE:
                return (in_array('ROLE_ADMIN', $user->getRoles()) || in_array('ROLE_BRAND', $user->getRoles())) && $user->getBrand();
                break;
        }

        return false;
    }
}