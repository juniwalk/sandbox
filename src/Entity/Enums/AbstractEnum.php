<?php declare(strict_types=1);

/**
 * @copyright Design Point, s.r.o. (c) 2020
 * @license   MIT License
 */

namespace App\Entity\Enums;

use App\Exceptions\InvalidEnumException;

abstract class AbstractEnum
{
	/** @var string[] */
	protected $items = [];


	/**
	 * @param  mixed  $item
	 * @return string
	 * @throws InvalidEnumException
	 */
	public function getItem($item): string
	{
		if (!$this->isValidItem($item)) {
			throw InvalidEnumException::fromItem($item);
		}

		return $this->items[$item];
	}


	/**
	 * @param  mixed  $item
	 * @return string
	 * @throws InvalidEnumException
	 */
	public function getShorthand($item): string
	{
		if (!$this->isValidItem($item)) {
			throw InvalidEnumException::fromItem($item);
		}

		return strtoupper(substr($item, 0, 1));
	}


	/**
	 * @param  bool  $withPlaceholder
	 * @return string[]
	 */
	public function getItems(bool $withPlaceholder = true): iterable
	{
		if ($withPlaceholder == true) {
			return $this->items;
		}

		return array_filter($this->items, function ($v, $k) {
			return $v && $k;
		},	ARRAY_FILTER_USE_BOTH);
	}


	/**
	 * @param  mixed  $value
	 * @return bool
	 */
	public function isValidItem($value): bool
	{
		return isset($this->items[$value]);
	}
}
