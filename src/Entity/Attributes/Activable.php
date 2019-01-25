<?php declare(strict_types=1);

/**
 * @copyright Design Point, s.r.o. (c) 2016
 * @license   MIT License
 */

namespace App\Entity\Attributes;

use Doctrine\ORM\Mapping as ORM;

trait Activable
{
	/**
	 * @ORM\Column(type="boolean", options={"default": 1})
	 * @var bool
	 */
	private $isActive = TRUE;


	/**
	 * @param bool  $active
	 */
	public function setActive(bool $active)
	{
		$this->isActive = $active;
	}


	/**
	 * @return bool
	 */
	public function isActive(): bool
	{
		return $this->isActive;
	}
}
