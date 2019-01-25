<?php declare(strict_types=1);

/**
 * @copyright Design Point, s.r.o. (c) 2016
 * @license   MIT License
 */

namespace App\Messages;

use App\Entity\User;
use App\Exceptions\UserNotFoundException;
use Nette\Mail\Message;
use Ublaboo\Mailing\IComposableMail;
use Ublaboo\Mailing\Mail;

final class UserSignUpMessage extends Mail implements IComposableMail
{
	/**
	 * @param  Message  $message
	 * @param  mixed[]  $params
	 * @throws UserNotFoundException
	 */
	public function compose(Message $message, $params = [])
	{
		$this->setTemplateFile(__DIR__.'/templates/userSignUp.latte');
		$user = $params['user'] ?? NULL;

		if (!$user || !$user instanceof User) {
			throw new \Exception;
		}

		$message->setFrom($this->mails['default_sender']);

        foreach ($this->mails['notify'] as $name => $email) {
    		$message->addTo($email, $name);
        }
	}
}
