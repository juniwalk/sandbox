<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2021
 * @license   MIT License
 */

namespace App\Security;

use App\Entity\User;
use App\Entity\UserRepository;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use Doctrine\ORM\NoResultException;
use Nette\Security\AuthenticationException;
use Nette\Security\Authenticator as IAuthenticator;
use Nette\Security\IdentityHandler;
use Nette\Security\IIdentity as Identity;
use Nette\Security\SimpleIdentity;

final class Authenticator implements IAuthenticator, IdentityHandler
{
	public function __construct(
		private readonly EntityManager $entityManager,
		private readonly UserRepository $userRepository,
	) {}


	/**
	 * @throws AuthenticationException
	 * @throws NoResultException
	 */
	public function authenticate(string $username, string $password): Identity
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


	function wakeupIdentity(Identity $identity): ?Identity
	{
		return $this->userRepository->getReference($identity->getId());
	}


	/**
	 * @throws AuthenticationException
	 */
	function sleepIdentity(Identity $identity): Identity
	{
		if (!$identity instanceof User) {
			throw new AuthenticationException('web.message.auth-invalid', $this::FAILURE);
		}

		return new SimpleIdentity($identity->getId());
	}
}
