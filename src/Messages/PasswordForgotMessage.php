<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2021
 * @license   MIT License
 */

namespace App\Messages;

use App\Entity\User;
use App\Exceptions\UserNotFoundException;
use Nette\Mail\Message;
use Ublaboo\Mailing\AbstractMail;
use Ublaboo\Mailing\IComposableMail;
use Ublaboo\Mailing\IMessageData;

final class PasswordForgotMessage extends AbstractMail implements IComposableMail
{
	/**
	 * @throws UserNotFoundException
	 */
	public function compose(Message $message, ?IMessageData $params): void
	{
		$user = $params->profile;

		if (!$user instanceof User) {
			throw new UserNotFoundException;
		}

		$message->setFrom($this->mailAddresses['default_sender']);
		$message->addTo($user->getEmail(), $user->getName());

		foreach ($params as $key => $value) {
			$this->template->add($key, $value);
		}
	}
}
