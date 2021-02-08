<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2020
 * @license   MIT License
 */

namespace App\Forms\Factory;

use App\Entity\User;
use App\Forms\AdminUserForm;

interface AdminUserFormFactory
{
    /**
     * @param  User|null  $user
     * @return AdminUserForm
     */
    public function create(?User $user): AdminUserForm;
}
