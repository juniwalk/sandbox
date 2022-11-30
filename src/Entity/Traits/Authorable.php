<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2020
 * @license   MIT License
 */

namespace App\Entity\Traits;

use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;

trait Authorable
{
	#[ORM\ManyToOne(targetEntity: User::class)]
	private ?User $author;


	public function setAuthor(?User $author): void
	{
		$this->author = $author;
	}


	public function getAuthor(): ?User
	{
		return $this->author;
	}


	public function isAuthor(?User $author): bool
	{
		if (!$author XOR !$this->author) {
			return false;
		}

		if (!$author && !$this->author) {
			return true;
		}

		return $this->author->getId() === $author->getId();
	}
}
