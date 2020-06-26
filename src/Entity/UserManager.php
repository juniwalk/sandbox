<?php declare(strict_types=1);

/**
 * @copyright Design Point, s.r.o. (c) 2020
 * @license   MIT License
 */

namespace App\Entity;

use App\Messages\MessageData;
use App\Messages\PasswordForgotMessage;
use App\Security\AccessManager;
use Contributte\ImageStorage\ImageStorage;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use Ublaboo\Mailing\MailFactory as MessageFactory;

final class UserManager
{
	/** @var MessageFactory */
	private $messageFactory;

	/** @var AccessManager */
	private $accessManager;

	/** @var EntityManager */
	private $entityManager;

	/** @var ImageStorage */
	private $imageStorage;


	/**
	 * @param ImageStorage  $imageStorage
     * @param AccessManager  $accessManager
	 * @param EntityManager  $entityManager
     * @param MessageFactory  $messageFactory
	 */
	public function __construct(
		ImageStorage $imageStorage,
		AccessManager $accessManager,
		EntityManager $entityManager,
		MessageFactory $messageFactory
	) {
		$this->messageFactory = $messageFactory;
		$this->entityManager = $entityManager;
		$this->accessManager = $accessManager;
		$this->imageStorage = $imageStorage;
	}


	/**
	 * @param  User  $user
	 * @return void
	 */
	public function imageRemove(User $user): void
	{
		if (!$image = $user->getImage()) {
			return;
		}

		try {
			$this->imageStorage->delete($image);
			$user->setImage(null);

			$this->entityManager->flush();

		} catch(ORMException $e) {
			// throw some custom exception
		}
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

		$message = $this->messageFactory->createByType(
			PasswordForgotMessage::class,
			MessageData::from([
				'profile' => $user,
				'hash' => $hash,
			])
		);

		try {
			$message->send();

		} catch(MailException $e) {
			// throw some custom exception
		}
	}
}
