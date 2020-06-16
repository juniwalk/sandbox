<?php declare(strict_types=1);

/**
 * @copyright Design Point, s.r.o. (c) 2020
 * @license   MIT License
 */

namespace App\Messages;

use App\Entity\User;
use App\Exceptions\UserNotFoundException;
use Nette\Mail\Message;
use Ublaboo\Mailing\IComposableMail;
use Ublaboo\Mailing\Mail;

final class PasswordForgotMessage extends Mail implements IComposableMail
{
	/**
	 * @param  Message  $message
	 * @param  mixed[]  $params
	 * @throws UserNotFoundException
	 */
	public function compose(Message $message, $params = [])
	{
		$this->setTemplateFile(__DIR__.'/templates/passwordForgot.latte');
		$user = $params['user'] ?? NULL;

		if (!$user || !$user instanceof User) {
			throw new UserNotFoundException;
		}

		$message->setFrom($this->mails['default_sender']);
		$message->addTo($user->getEmail(), $user->getName());
	}
}
