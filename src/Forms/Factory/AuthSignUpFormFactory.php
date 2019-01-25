<?php declare(strict_types=1);

/**
 * @copyright Design Point, s.r.o. (c) 2016
 * @license   MIT License
 */

namespace App\Forms\Factory;

use App\Forms\AuthSignUpForm;

interface AuthSignUpFormFactory
{
    /**
     * @return AuthSignUpForm
     */
    public function create(): AuthSignUpForm;
}
