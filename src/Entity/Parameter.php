<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2020
 * @license   MIT License
 */

namespace App\Entity;

use App\Exception\InvalidValueException;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use Nette\Utils\Strings;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_param", uniqueConstraints={@ORM\UniqueConstraint(name="user_unique", columns={"user_id", "key"})})
 * @ORM\HasLifecycleCallbacks
 */
class Parameter
{
	use Attributes\Ownership;
	use Attributes\Timestamp;

	/**
	 * @ORM\ManyToOne(targetEntity="User")
	 * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
	 * @ORM\Id
	 * @var User
	 */
	private $user;

    /**
     * @ORM\Column(type="string", length=64)
	 * @ORM\Id
     * @var string|null
     */
    private $key;

    /**
     * @ORM\Column(type="json", nullable=true)
     * @var string
     */
	private $value;


	/**
	 * @param  string  $key
	 * @param  mixed  $value
	 * @param  User  $user
	 */
	public function __construct(string $key, $value, User $user)
	{
		$this->created = new DateTime;
		$this->key = Strings::lower($key);
		$this->value = $value ?: null;
		$this->user = $user;
	}


	/**
	 * @return string
	 */
	public function getId(): string
	{
		return $this->user->getId().'-'.$this->key;
	}


	/**
	 * @return string
	 */
	public function getKey(): string
	{
		return $this->key;
	}


	/**
	 * @param  mixed  $value
	 * @return void
	 * @throws InvalidValueException
	 */
	public function setValue($value): void
	{
		if (is_object($value) && !$value instanceof JsonSerializable) {
			throw new InvalidValueException('Object instances have to implement JsonSerializable');
		}

		$this->value = $value ?: null;
	}


	/**
	 * @return mixed
	 */
	public function getValue()
	{
		return $this->value;
	}
}
