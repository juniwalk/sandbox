<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2020
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
		$this->allow(Role::GUEST, 'Error4xx');
		$this->allow(Role::GUEST, 'Web:Auth');

		// User rules
		$this->allow(Role::USER, 'Web:Home');

		// Manager rules
		$this->allow(Role::MANAGER, 'Admin:Home');
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
		$this->addResource('Error4xx');
		$this->addResource('Web:Auth');

		// User resources
		$this->addResource('Web:Home');

		// Manager resources

		// Admin resources
		$this->addResource('Admin:Home');
		$this->addResource('Admin:User');
	}
}
