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
use JuniWalk\Utils\ORM\Traits as TraitsUtils;
use JuniWalk\Utils\Strings;

#[ORM\Entity]
#[ORM\Table(name: 'user_param')]
#[ORM\HasLifecycleCallbacks]
#[ORM\UniqueConstraint(name: 'param_unique', columns: ['user_id', 'key'])]
class Parameter
{
	use Traits\Ownership;
	use TraitsUtils\Timestamp;

	#[ORM\ManyToOne(targetEntity: User::class)]
	#[ORM\JoinColumn(nullable: false)]
	#[ORM\Id]
	private User $user;

	#[ORM\Column(type: 'string', length: 64)]
	#[ORM\Id]
	private ?string $key;

	#[ORM\Column(type: 'json', nullable: true)]
	private mixed $value;


	public function __construct(string $key, mixed $value, User $user)
	{
		$this->created = new DateTime;
		$this->key = Strings::lower($key);
		$this->value = $value ?: null;
		$this->user = $user;
	}


	public function getId(): string
	{
		return $this->user->getId().'-'.$this->key;
	}


	public function getKey(): string
	{
		return $this->key;
	}


	/**
	 * @throws InvalidValueException
	 */
	public function setValue(mixed $value): void
	{
		if (is_object($value) && !$value instanceof JsonSerializable) {
			throw new InvalidValueException('Object instances have to implement JsonSerializable');
		}

		$this->value = $value ?: null;
	}


	public function getValue(): mixed
	{
		return $this->value;
	}
}
