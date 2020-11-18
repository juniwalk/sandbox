<?php declare(strict_types=1);

/**
 * @copyright Design Point, s.r.o. (c) 2020
 * @license   MIT License
 */

namespace App\Entity\Attributes;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

trait Timestamp
{
	/**
	 * @ORM\Column(type="datetimetz")
	 * @var DateTime
	 */
	private $created;

	/**
	 * @ORM\Column(type="datetimetz", nullable=true)
	 * @var DateTime|null
	 */
	private $modified;


	/**
	 * @return DateTime
	 */
	public function getCreated(): DateTime
	{
		return clone $this->created;
	}


	/**
	 * @param DateTime|null  $modified
	 * @return void
	 */
	public function setModified(?DateTime $modified): void
	{
		$this->modified = $modified ? clone $modified : new DateTime;
	}


	/**
	 * @return DateTime|null
	 */
	public function getModified(): ?DateTime
	{
		if (!$this->modified) {
			return null;
		}

		return clone $this->modified;
	}


	/**
	 * @return DateTime
	 */
	public function getTimestamp(): DateTime
	{
		return clone ($this->modified ?: $this->created);
	}


	/**
	 * @ORM\PreUpdate
	 * @return void
	 * @internal
	 */
	public function onUpdate(): void
	{
		$this->modified = new DateTime;
	}


    /**
     * @ORM\PrePersist
	 * @return void
	 * @internal
     */
    public function onPersist(): void
    {
        $this->created = new DateTime;
    }
}
