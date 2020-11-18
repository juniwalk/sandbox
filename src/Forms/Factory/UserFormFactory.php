<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2020
 * @license   MIT License
 */

namespace App\Forms\Factory;

use App\Entity\User;
use App\Forms\UserForm;

interface UserFormFactory
{
    /**
     * @param  User|null  $user
     * @return UserForm
     */
    public function create(?User $user): UserForm;
}
