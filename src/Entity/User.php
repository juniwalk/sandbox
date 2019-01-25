<?php declare(strict_types=1);

/**
 * @copyright Design Point, s.r.o. (c) 2016
 * @license   MIT License
 */

namespace App\Entity;

use App\Entity\Enums\Role;
use App\Exceptions\InvalidEnumException;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Nette\Security\Passwords;
use Nette\Utils\Strings;

/**
 * @ORM\Entity
 * @ORM\Table(name="`user`")
 */
class User implements \Nette\Security\IIdentity
{
	use Attributes\Identifier;
	use Attributes\Activable;


    /**
     * @ORM\Column(type="string", length=64)
     * @var string
     */
    private $name;

    /**
     * @ORM\Column(type="string", unique=true)
     * @var string
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     * @var string|NULL
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=32)
     * @var string
     */
	private $role = Role::USER;

    /**
     * @ORM\Column(type="datetimetz", options={"default": "CURRENT_TIMESTAMP"})
     * @var DateTime
     */
    private $signUp;

    /**
     * @ORM\Column(type="datetimetz", nullable=true)
     * @var DateTime|NULL
     */
    private $signIn;


	/**
	 * @param  string  $email
	 * @param  string  $name
	 */
	public function __construct(string $email, string $name)
	{
		$this->signUp = new DateTime;
		$this->email = Strings::lower($email);
		$this->name = $name;
	}


	/**
	 * @return string
	 */
	public function __toString(): string
	{
		return Strings::webalize($this->id.'-'.$this->name);
	}


	/**
	 * @param  string  $name
	 * @return void
	 */
	public function setName(string $name): void
	{
		$this->name = $name;
	}


	/**
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}


	/**
	 * @param  string  $email
	 * @return void
	 */
	public function setEmail(string $email): void
	{
		$this->email = Strings::lower($email);
	}


	/**
	 * @return string
	 */
	public function getEmail(): string
	{
		return $this->email;
	}


	/**
	 * @param  string|NULL  $password
	 * @return void
	 */
	public function setPassword(?string $password): void
	{
        if (!empty($password)) {
            $password = Passwords::hash($password);
        }

		$this->password = $password ?: NULL;
	}


	/**
	 * @param  string  $password
	 * @return bool
	 */
	public function isPasswordValid(string $password): bool
	{
		return Passwords::verify($password, $this->password);
	}


	/**
	 * @return bool
	 */
	public function isPasswordUpToDate(): bool
	{
		return !Passwords::needsRehash($this->password);
	}


	/**
	 * @param  string  $role
	 * @return void
	 * @throws InvalidEnumException
	 */
	public function setRole(string $role): void
	{
		if (!(new Role)->isValidItem($role)) {
			throw InvalidEnumException::fromItem($role);
		}

		$this->role = $role;
	}


	/**
	 * @return string
	 */
	public function getRole(): string
	{
		return $this->role;
	}


	/**
	 * @return string[]
	 */
	public function getRoles(): iterable
	{
		return [$this->role];
	}


	/**
	 * @return DateTime
	 */
	public function getSignUp(): DateTime
	{
		return clone $this->signUp;
	}


	/**
	 * @param  DateTime|NULL  $signIn
	 * @return void
	 */
	public function setSignIn(?DateTime $signIn): void
	{
		$signIn = $signIn ?: new DateTime;
		$this->signIn = clone $signIn;
	}


	/**
	 * @return DateTime|NULL
	 */
	public function getSignin(): ?DateTime
	{
		if (!$this->signIn) {
			return NULL;
		}

		return clone $this->signIn;
	}
}
