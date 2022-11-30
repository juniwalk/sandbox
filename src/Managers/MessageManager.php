<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2020
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
	private ?User $profile;


	public function __construct(
		LoggedInUser $loggedInUser,
		private MessageFactory $messageFactory
	) {
		$this->profile = $loggedInUser->getIdentity();
		$this->messageFactory = $messageFactory;
	}


	/**
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
	 * @throws MailingException
	 */
	public function sendPasswordForgotMessage(string $hash, ?User $user): void
	{
		$message = $this->createByType(PasswordForgotMessage::class, [
			'profile' => $user,
			'hash' => $hash,
		]);

		$message->send();
	}


	private function createByType(string $class, array $params = []): Message
	{
		$params = $params + [
			'company' => $this->company,
			'profile' => $this->profile,
		];

		return $this->messageFactory->createByType($class, MessageData::from($params));
	}
}
