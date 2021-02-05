<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2020
 * @license   MIT License
 */

namespace App\Security;

use App\Entity\User;
use App\Entity\UserRepository;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use Nette\Security\AuthenticationException;
use Nette\Security\Authenticator as IAuthenticator;
use Nette\Security\IdentityHandler;
use Nette\Security\IIdentity;
use Nette\Security\SimpleIdentity;

final class Authenticator implements IAuthenticator, IdentityHandler
{
	/** @var EntityManager */
	private $entityManager;

	/** @var UserRepository */
	private $userRepository;


	/**
	 * @param EntityManager  $entityManager
	 * @param UserRepository  $userRepository
	 */
	public function __construct(
		EntityManager $entityManager,
		UserRepository $userRepository
	) {
		$this->userRepository = $userRepository;
		$this->entityManager = $entityManager;
	}


	/**
	 * @param  string  $username
	 * @param  string  $password
	 * @return IIdentity
	 * @throws AuthenticationException
	 */
	public function authenticate(string $username, string $password): IIdentity
	{
		$user = $this->userRepository->getByEmail($username);
		$user->setSignIn(null);

		if (!$user->isPasswordValid($password)) {
			throw new AuthenticationException('web.message.auth-invalid', $this::INVALID_CREDENTIAL);
		}

		if (!$user->isActive()) {
			throw new AuthenticationException('web.message.auth-banned', $this::NOT_APPROVED);
		}

		if (!$user->isPasswordUpToDate()) {
			$user->setPassword($password);
		}

		$this->entityManager->flush();

		return $user;
	}


	/**
	 * @param  IIdentity  $identity
	 * @return IIdentity|null
	 */
	function wakeupIdentity(IIdentity $identity): ?IIdentity
	{
		return $this->userRepository->getReference($identity->getId());
	}


	/**
	 * @param  IIdentity  $identity
	 * @return IIdentity
	 * @throws AuthenticationException
	 */
	function sleepIdentity(IIdentity $identity): IIdentity
	{
		if (!$identity instanceof User) {
			throw new AuthenticationException('web.message.auth-invalid', $this::FAILURE);
		}

		return new SimpleIdentity($identity->getId());
	}
}
