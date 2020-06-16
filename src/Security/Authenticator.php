<?php declare(strict_types=1);

/**
 * @copyright Design Point, s.r.o. (c) 2020
 * @license   MIT License
 */

namespace App\Security;

use App\Entity\UserRepository;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use Nette\Security\AuthenticationException;
use Nette\Security\IAuthenticator;
use Nette\Security\IIdentity;

final class Authenticator implements IAuthenticator
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
	 * @param  string[]  $credentials
	 * @return IIdentity
	 * @throws AuthenticationException
	 */
	public function authenticate(iterable $credentials): IIdentity
	{
		list($username, $password) = $credentials;

		$user = $this->userRepository->getByEmail($username);
		$user->setSignIn(null);

		if (!$user->isPasswordValid($password)) {
			throw new AuthenticationException('nette.message.auth-invalid', $this::INVALID_CREDENTIAL);
		}

		if (!$user->isActive()) {
			throw new AuthenticationException('nette.message.auth-banned', $this::NOT_APPROVED);
		}

		if (!$user->isPasswordUpToDate()) {
			$user->setPassword($password);
		}

		$this->entityManager->flush();

		return $user;
	}
}
