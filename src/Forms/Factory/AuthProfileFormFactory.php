<?php declare(strict_types=1);

/**
 * @copyright Design Point, s.r.o. (c) 2020
 * @license   MIT License
 */

namespace App\Forms\Factory;

use App\Entity\User;
use App\Forms\AuthProfileForm;

interface AuthProfileFormFactory
{
    /**
     * @param  User  $user
     * @return AuthProfileForm
     */
    public function create(User $user): AuthProfileForm;
}
