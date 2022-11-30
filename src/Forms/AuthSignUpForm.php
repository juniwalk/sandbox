<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2020
 * @license   MIT License
 */

namespace App\Forms;

use App\Managers\MessageManager;
use App\Managers\UserManager;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use JuniWalk\Form\AbstractForm;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use Throwable;
use Tracy\Debugger;

final class AuthSignUpForm extends AbstractForm
{
	public function __construct(
		private UserManager $userManager,
		private EntityManager $entityManager,
		private MessageManager $messageManager
	) {}


	protected function createComponentForm(string $name): Form
	{
		$form = parent::createComponentForm($name);
		$form->addText('email')->setRequired('web.user.email-required')
			->addRule($form::EMAIL, 'web.user.email-invalid');
		$form->addPassword('password')->setRequired('web.user.password-required')
			->addRule($form::MIN_LENGTH, 'web.user.password-length', 6);
		$form->addReCaptcha('recaptcha')->setRequired('web.user.captcha-required');

		$form->addSubmit('submit');

		return $form;
	}


	protected function handleSuccess(Form $form, ArrayHash $data): void
	{
		try {
			$user = $this->userManager->createUser(
				$data->email,
				$data->password,
				true
			);

			$this->entityManager->persist($user);
			$this->entityManager->flush();

			$this->messageManager->sendUserSignUpMessage($user);

		} catch(UniqueConstraintViolationException) {
			$form['email']->addError('web.message.auth-email-used');

		} catch (Throwable $e) {
			$form->addError('web.message.something-went-wrong');
			Debugger::log($e);
		}
	}
}
