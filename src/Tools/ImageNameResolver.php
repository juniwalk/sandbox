<?php declare(strict_types=1);

/**
 * @copyright Design Point, s.r.o. (c) 2016
 * @license   MIT License
 */

namespace App\Tools;

use App\Entity\User;
use Carrooi\ImagesManager\Naming\INameResolver;

final class ImageNameResolver implements INameResolver
{
	/**
	 * @param  mixed  $entity
	 * @return string
	 */
	public function getName($entity): string
	{
		if ($entity instanceof User) {
			return $entity.'.jpg';
		}

		return (string) $entity;
	}


	/**
	 * @param  mixed  $entity
	 * @return string|NULL
	 */
	public function getDefaultName($entity): ?string
	{
		return NULL;
	}
}
