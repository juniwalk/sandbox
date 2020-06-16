<?php declare(strict_types=1);

/**
 * @copyright Design Point, s.r.o. (c) 2020
 * @license   MIT License
 */

namespace App\Entity\Enums;

final class Status extends AbstractEnum
{
	/** @var string */
	const CREATED = 'created';
	const DELIVERED = 'delivered';
    const STORNO = 'storno';
    const UNKNOWN = 'unknown';


	/** @var string[] */
	protected $items = [
		self::CREATED => 'errest.order.statuses.created',
		self::DELIVERED => 'errest.order.statuses.delivered',
		self::STORNO => 'errest.order.statuses.storno',
		self::UNKNOWN => 'errest.order.statuses.unknown',
	];
}
