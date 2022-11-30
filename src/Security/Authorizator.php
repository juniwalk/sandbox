<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2020
 * @license   MIT License
 */

namespace App\Security;

use JuniWalk\Utils\Enums\Role;
use Nette\Security\Permission;

final class Authorizator extends Permission
{
	public function __construct()
	{
		foreach (Role::getMap() as $role => $parent) {
			$this->addRole($role, $parent);
		}

		$this->createResources();
		$this->grantPrivileges();
	}


	private function grantPrivileges(): void
	{
		// Guest rules
		$this->allow(Role::Guest->value, 'Error4xx');
		$this->allow(Role::Guest->value, 'Web:Auth');

		// User rules
		$this->allow(Role::User->value, 'Web:Home');

		// Manager rules
		$this->allow(Role::Manager->value, 'Admin:Home');
		$this->allow(Role::Manager->value, 'Admin:User');

		// Admin has access anywhere
		$this->allow(Role::Admin->value);
	}


	private function createResources(): void
	{
		// Root resources
		$this->addResource('Error4xx');

		// Web resources
		$this->addResource('Web:Auth');
		$this->addResource('Web:Home');

		// Admin resources
		$this->addResource('Admin:Home');
		$this->addResource('Admin:User');
	}
}
