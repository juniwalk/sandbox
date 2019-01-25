<?php declare(strict_types=1);

/**
 * @copyright Design Point, s.r.o. (c) 2016
 * @license   MIT License
 */

namespace App\Forms\Factory;

use App\Entity\User;
use App\Forms\ProfileForm;

interface ProfileFormFactory
{
    /**
     * @param  User  $user
     * @return ProfileForm
     */
    public function create(User $user): ProfileForm;
}
