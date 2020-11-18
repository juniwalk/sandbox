<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2020
 * @license   MIT License
 */

namespace App\Managers;

use App\Entity\User;
use App\Security\AccessManager;
use Doctrine\ORM\EntityManagerInterface as EntityManager;

final class UserManager
{
	/** @var MessageManager */
	private $messageManager;

	/** @var AccessManager */
	private $accessManager;

	/** @var EntityManager */
	private $entityManager;


	/**
	 * @param EntityManager  $entityManager
     * @param AccessManager  $accessManager
     * @param MessageManager  $messageManager
	 */
	public function __construct(
		EntityManager $entityManager,
		AccessManager $accessManager,
		MessageManager $messageManager
	) {
		$this->messageManager = $messageManager;
		$this->accessManager = $accessManager;
		$this->entityManager = $entityManager;
	}


	/**
     * @param  string  $email
     * @param  string  $password
	 * @param  bool  $activationRequired
	 * @return User
	 */
	public function createUser(string $email, string $password, bool $activationRequired = false): User
	{
    	$user = new User($email);
		$user->setPassword($password);

		if ($activationRequired == true) {
			$this->deactivateUser($user);
		}

		return $user;
	}


	/**
	 * @param  User  $user
	 * @return void
	 * @throws ORMException
	 */
	public function deactivateUser(User $user): void
	{
		$params = ['_slug' => 'Web:Auth:activate'];
		$email = $user->getEmail();

		$hash = $this->accessManager->createToken($email, $params, [
			'expire' => '1 hour',
		]);

		$user->setParam('activate', $hash);
	}


	/**
	 * @param  User  $user
	 * @param  string  $hash
	 * @return void
	 * @throws ORMException
	 */
	public function activateUser(User $user, string $hash): void
	{
		if ($hash !== $user->getParam('activate')) {
			throw new ForbiddenRequestException('Hash invalid');
		}

		$user->setParam('activate', null);
		$this->entityManager->flush();
	}


	/**
	 * @param  User  $user
	 * @return void
	 */
	public function passwordForgot(User $user): void
	{
		$hash = $this->accessManager->createSluggedToken($user, 'Web:Auth:profile', [
			'expire' => '15 minutes',
		]);

		$this->messageManager->sendPasswordForgotMessage($hash, $user);
	}
}
