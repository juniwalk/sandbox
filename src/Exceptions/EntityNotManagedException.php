<?php declare(strict_types=1);

/**
 * @copyright Design Point, s.r.o. (c) 2020
 * @license   MIT License
 */

namespace App\Exceptions;

final class EntityNotManagedException extends AbstractException
{
	/**
	 * @param  object  $entity
	 * @return static
	 */
	public static function fromEntity($entity): self
	{
		return new static('Entity '.get_class($entity).' is not managed in EntityManager.');
	}
}
