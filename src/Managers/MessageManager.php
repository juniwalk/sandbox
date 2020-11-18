<?php declare(strict_types=1);

/**
 * @copyright Design Point, s.r.o. (c) 2020
 * @license   MIT License
 */

namespace App\Managers;

use App\Entity\User;
use App\Messages\MessageData;
use App\Messages\PasswordForgotMessage;
use App\Messages\UserSignUpMessage;
use Nette\Security\User as LoggedInUser;
use Ublaboo\Mailing\IComposableMail as Message;
use Ublaboo\Mailing\Exception\MailingException;
use Ublaboo\Mailing\MailFactory as MessageFactory;

final class MessageManager
{
	/** @var MessageFactory */
	private $messageFactory;

	/** @var User */
	private $profile;


	/**
	 * @param LoggedInUser  $user
     * @param MessageFactory  $messageFactory
	 */
	public function __construct(
		LoggedInUser $user,
		MessageFactory $messageFactory
	) {
		$this->profile = $user->getIdentity();
		$this->messageFactory = $messageFactory;
	}


	/**
	 * @param  User  $user
	 * @return void
	 * @throws MailingException
	 */
	public function sendUserSignUpMessage(User $user): void
	{
		$message = $this->createByType(UserSignUpMessage::class, [
			'hash' => $user->getParam('activate'),
			'profile' => $user,
		]);

		$message->send();
	}


	/**
	 * @param  string  $hash
	 * @param  User|null  $user
	 * @return void
	 * @throws MailingException
	 */
	public function sendPasswordForgotMessage(string $hash, ?User $user): void
	{
		$message = $this->createByType(PasswordForgotMessage::class, [
			'hash' => $hash,
			'profile' => $user,
		]);

		$message->send();
	}


	/**
	 * @param  string  $class
	 * @param  mixed[]  $params
	 * @return Message
	 */
	private function createByType(string $class, iterable $params = []): Message
	{
		$params = $params + [
			'profile' => $this->profile,
		];

		return $this->messageFactory->createByType($class, MessageData::from($params));
	}
}
