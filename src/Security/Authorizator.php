<?php declare(strict_types=1);

/**
 * @copyright Design Point, s.r.o. (c) 2016
 * @license   MIT License
 */

namespace App\Security;

use App\Entity\Enums\Role;
use Nette\Security\Permission;

final class Authorizator extends Permission
{
	public function __construct()
	{
		foreach ((new Role)->getMap() as $role => $parent) {
			$this->addRole($role, $parent);
		}

		$this->createResources();
		$this->grantPrivileges();
	}


	/**
	 * @return void
	 */
	private function grantPrivileges(): void
	{
		// Guest rules
		$this->allow(Role::GUEST, 'Auth');
		$this->allow(Role::GUEST, 'Error4xx');

		// User rules
		$this->allow(Role::USER, 'Homepage');

		// Manager rules
		$this->allow(Role::MANAGER, 'Admin:User');

		// Admin has access anywhere
		$this->allow(Role::ADMIN);
	}


	/**
	 * @return void
	 */
	private function createResources(): void
	{
		// Guest resources
		$this->addResource('Auth');
		$this->addResource('Error4xx');

		// User resources
		$this->addResource('Homepage');

		// Manager resources

		// Admin resources
		$this->addResource('Admin:User');
	}
}
