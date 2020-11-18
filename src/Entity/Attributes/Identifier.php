<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2020
 * @license   MIT License
 */

namespace App\Entity\Attributes;

use Doctrine\ORM\Mapping as ORM;

trait Identifier
{
	/**
	 * @ORM\Column(type="integer", unique=true, nullable=false)
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 * @ORM\Id
	 * @var int
	 */
	private $id;


	/**
	 * @return int|null
	 */
	final public function getId(): ?int
	{
		return $this->id;
	}


	public function __clone()
	{
		$this->id = null;
	}
}
