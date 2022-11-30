<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2020
 * @license   MIT License
 */

namespace App\Managers;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface as EntityManager;

final class UserManager
{
	public function __construct(
		private AccessManager $accessManager,
		private EntityManager $entityManager,
		private MessageManager $messageManager,
	) {}


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
	 * @throws ForbiddenRequestException
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


	public function passwordForgot(User $user): void
	{
		$hash = $this->accessManager->createSluggedToken($user, 'Auth:profile', [
			'expire' => '15 minutes',
		]);

		$this->messageManager->sendPasswordForgotMessage($hash, $user);
	}
}
