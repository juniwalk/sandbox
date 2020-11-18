<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2020
 * @license   MIT License
 */

namespace App\Exceptions;

final class InvalidEnumException extends AbstractException
{
	/**
	 * @param  mixed  $item
	 * @return static
	 */
	public static function fromItem($item): self
	{
		return new static('Item '.$item.' is not valid Enum.');
	}
}
