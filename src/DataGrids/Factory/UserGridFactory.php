<?php declare(strict_types=1);

/**
 * @copyright Design Point, s.r.o. (c) 2016
 * @license   MIT License
 */

namespace App\DataGrids\Factory;

use App\DataGrids\UserGrid;

interface UserGridFactory
{
	/**
	 * @return UserGrid
	 */
	public function create(): UserGrid;
}
