<?php

// src/Service/UserRoleComparator.php
namespace App\Service;

use App\Entity\User;

class UserRoleComparator
{
    private const ROLE_HIERARCHY = [
        'ROLE_USER' => 1,
        'ROLE_ADMIN' => 2,
        'ROLE_SUPER_ADMIN' => 3,
    ];

    /**
     * Retourne le niveau le plus élevé d'un utilisateur donné.
     */
    private function getHighestLevel(User $user): int
    {
        $max = 0;
        foreach ($user->getRoles() as $role) {
            $level = self::ROLE_HIERARCHY[$role] ?? 0;
            if ($level > $max) {
                $max = $level;
            }
        }
        return $max;
    }

    /**
     * Vérifie si $a est strictement supérieur à $b.
     */
    public function isSuperior(User $a, User $b): bool
    {
        return $this->getHighestLevel($a) > $this->getHighestLevel($b);
    }

    /**
     * Vérifie si $a a un niveau supérieur ou égal à $b.
     */
    public function isEqualOrSuperior(User $a, User $b): bool
    {
        return $this->getHighestLevel($a) >= $this->getHighestLevel($b);
    }
}
