<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2020
 * @license   MIT License
 */

namespace App\Forms\Factory;

use App\Entity\User;
use App\Forms\AuthProfileForm;

interface AuthProfileFormFactory
{
	public function create(User $user): AuthProfileForm;
}
