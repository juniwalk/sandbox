<?php declare(strict_types=1);

/**
 * @copyright Design Point, s.r.o. (c) 2020
 * @license   MIT License
 */

namespace App\Forms\Factory;

use App\Forms\AuthPasswordForgotForm;

interface AuthPasswordForgotFormFactory
{
    /**
     * @return AuthPasswordForgotForm
     */
    public function create(): AuthPasswordForgotForm;
}
