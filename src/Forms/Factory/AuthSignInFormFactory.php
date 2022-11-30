<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2020
 * @license   MIT License
 */

namespace App\Forms\Factory;

use App\Forms\AuthSignInForm;

interface AuthSignInFormFactory
{
	public function create(): AuthSignInForm;
}
