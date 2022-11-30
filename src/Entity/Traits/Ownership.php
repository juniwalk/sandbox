<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2020
 * @license   MIT License
 */

namespace App\Entity\Traits;

use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;

trait Ownership
{
	#[ORM\ManyToOne(targetEntity: User::class)]
	#[ORM\JoinColumn(nullable: false)]
	private User $user;


	public function setOwner(User $owner): void
	{
		$this->user = $owner;
	}


	public function getOwner(): User
	{
		return $this->user;
	}


	#[\Deprecated('Use getOwner instead')]
	public function getUser(): User
	{
		return $this->getOwner();
	}


	public function isOwner(User $owner): bool
	{
		return $this->user->getId() === $owner->getId();
	}
}
