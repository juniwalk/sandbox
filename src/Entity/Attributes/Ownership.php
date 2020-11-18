<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2020
 * @license   MIT License
 */

namespace App\Entity\Attributes;

use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;

trait Ownership
{
	/**
	 * @ORM\ManyToOne(targetEntity="User")
	 * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
	 * @var User
	 */
	private $user;


	/**
	 * @param  User  $user
	 * @return void
	 */
	public function setUser(User $user): void
	{
		$this->user = $user;
	}


	/**
	 * @param  User  $user
	 * @return bool
	 */
	public function hasUser(User $user): bool
	{
		return $this->user->getId() === $user->getId();
	}


	/**
	 * @return User
	 */
	public function getUser(): User
	{
		return $this->user;
	}
}
