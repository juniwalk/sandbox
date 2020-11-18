<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2020
 * @license   MIT License
 */

namespace App\Entity;

use App\Entity\Enums\Role;
use App\Exceptions\InvalidEnumException;
use Contributte\ImageStorage\Image;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Nette\Security\IIdentity as Identity;
use Nette\Utils\Strings;

/**
 * @ORM\Entity
 * @ORM\Table(name="`user`")
 */
class User implements Identity
{
	use Attributes\Identifier;
	use Attributes\Parametrized;
	use Attributes\Activable;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     * @var string|null
     */
    private $name;

    /**
     * @ORM\Column(type="string", unique=true)
     * @var string
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     * @var string|null
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=32)
     * @var string
     */
	private $role = Role::USER;

    /**
     * @ORM\Column(type="datetimetz", options={"default": "now()"})
     * @var DateTime
     */
    private $signUp;

    /**
     * @ORM\Column(type="datetimetz", nullable=true)
     * @var DateTime|null
     */
    private $signIn;


	/**
	 * @param  string  $email
	 * @param  string  $name
	 */
	public function __construct(string $email, string $name = null)
	{
		$this->params = new ArrayCollection;
		$this->signUp = new DateTime;

		$this->email = Strings::lower($email);
		$this->name = $name;
	}


	/**
	 * @return string
	 */
	public function __toString(): string
	{
		return Strings::webalize($this->id.'-'.$this->email);
	}


	/**
	 * @param  string|null  $name
	 * @return void
	 */
	public function setName(?string $name): void
	{
		$this->name = $name;
	}


	/**
	 * @return string|null
	 */
	public function getName(): ?string
	{
		return $this->name;
	}


	/**
	 * @return string
	 */
	public function getDisplayName(): string
	{
		return $this->name ?: $this->email;
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
	 * @return bool
	 */
	public function isEmailActivated(): bool
	{
		return ! (bool) $this->getParam('activate');
	}


	/**
	 * @param  string|null  $password
	 * @return void
	 */
	public function setPassword(?string $password): void
	{
        if (!empty($password)) {
            $password = password_hash($password, PASSWORD_DEFAULT);
        }

		$this->password = $password ?: null;
	}


	/**
	 * @param  string  $password
	 * @return bool
	 */
	public function isPasswordValid(string $password): bool
	{
		return password_verify($password, $this->password);
	}


	/**
	 * @return bool
	 */
	public function isPasswordUpToDate(): bool
	{
		return !password_needs_rehash($this->password, PASSWORD_DEFAULT);
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
	public function getRoles(): array
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
	 * @param  DateTime|null  $signIn
	 * @return void
	 */
	public function setSignIn(?DateTime $signIn): void
	{
		$signIn = $signIn ?: new DateTime;
		$this->signIn = clone $signIn;
	}


	/**
	 * @return DateTime|null
	 */
	public function getSignin(): ?DateTime
	{
		if (!$this->signIn) {
			return null;
		}

		return clone $this->signIn;
	}
}
