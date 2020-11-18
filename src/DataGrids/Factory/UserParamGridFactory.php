<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2020
 * @license   MIT License
 */

namespace App\DataGrids\Factory;

use App\Entity\User;
use App\DataGrids\UserParamGrid;

interface UserParamGridFactory
{
	/**
	 * @param  User  $user
	 * @return UserParamGrid
	 */
	public function create(User $user): UserParamGrid;
}
