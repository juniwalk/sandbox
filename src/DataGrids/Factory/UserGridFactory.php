<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2020
 * @license   MIT License
 */

namespace App\DataGrids\Factory;

use App\DataGrids\UserGrid;

interface UserGridFactory
{
	public function create(): UserGrid;
}
