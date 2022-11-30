<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace App\Exceptions;

final class PermissionDeniedException extends AbstractException
{
	public static function fromTask(string $resource, string $task, self $previous = null): self
	{
		return new self('Access denied for task '.$resource.':'.$task, 403, $previous);
	}
}
