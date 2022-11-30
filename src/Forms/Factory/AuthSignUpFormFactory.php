<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2020
 * @license   MIT License
 */

namespace App\Forms\Factory;

use App\Forms\AuthSignUpForm;

interface AuthSignUpFormFactory
{
	public function create(): AuthSignUpForm;
}
