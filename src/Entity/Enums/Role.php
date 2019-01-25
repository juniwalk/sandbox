<?php declare(strict_types=1);

/**
 * @copyright Design Point, s.r.o. (c) 2016
 * @license   MIT License
 */

namespace App\Entity\Enums;

final class Role extends AbstractEnum
{
	/** @var string */
	const GUEST = 'guest';
	const USER = 'user';
	const MANAGER = 'manager';
	const ADMIN = 'admin';


	/** @var string[] */
	protected $items = [
		self::GUEST => 'nette.user.roles.guest',
		self::USER => 'nette.user.roles.user',
		self::MANAGER => 'nette.user.roles.manager',
		self::ADMIN => 'nette.user.roles.admin',
	];

	/**
	 * Map of roles with parent assignment.
	 * @var string[]
	 */
	private $map = [
		self::GUEST => NULL,
		self::USER => self::GUEST,
		self::MANAGER => self::USER,
		self::ADMIN => NULL,
	];


	/**
	 * @return string[]
	 */
	public function getMap(): iterable
	{
		return $this->map;
	}
}
