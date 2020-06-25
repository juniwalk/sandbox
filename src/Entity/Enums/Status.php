<?php declare(strict_types=1);

/**
 * @copyright Design Point, s.r.o. (c) 2020
 * @license   MIT License
 */

namespace App\Entity\Enums;

use App\Exceptions\InvalidEnumException;

final class Status extends AbstractEnum
{
	/** @var string */
	const CREATED = 'created';
	const ACTIVE = 'active';
    const FINISH = 'finish';
    const INVOICE = 'invoice';
    const DELETE = 'deleted';

	/** @var string[] */
	protected $items = [
		self::CREATED => 'nette.enum.status.created',
		self::ACTIVE => 'nette.enum.status.active',
		self::FINISH => 'nette.enum.status.finish',
		self::INVOICE => 'nette.enum.status.invoice',
		self::DELETE => 'nette.enum.status.delete',
	];

	/** @var string[] */
	private $colors = [
		self::CREATED => 'warning',
		self::ACTIVE => 'info',
		self::FINISH => 'success',
		self::INVOICE => 'primary',
		self::DELETE => 'default',
	];


	/**
	 * @param  string  $item
	 * @return string
	 * @throws InvalidEnumException
	 */
	public function getColor(string $item): string
	{
		if (!$this->isValidItem($item)) {
			throw InvalidEnumException::fromItem($item);
		}

		return $this->colors[$item];
	}
}
