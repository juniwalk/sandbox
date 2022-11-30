<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2020
 * @license   MIT License
 */

namespace App\Forms;

use App\Entity\UserRepository;
use App\Managers\UserManager;
use Doctrine\ORM\NoResultException;
use JuniWalk\Form\AbstractForm;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use Throwable;
use Tracy\Debugger;

final class AuthPasswordForgotForm extends AbstractForm
{
	public function __construct(
		private UserManager $userManager,
		private UserRepository $userRepository
	) {}


	protected function createComponentForm(string $name): Form
	{
		$form = parent::createComponentForm($name);
		$form->addText('email')->setRequired('nette.user.email-required')
			->addRule($form::EMAIL, 'nette.user.email-invalid');
		$form->addReCaptcha('recaptcha')->setRequired('nette.user.captcha-required');

		$form->addSubmit('submit');

		return $form;
	}


	protected function handleSuccess(Form $form, ArrayHash $data): void
	{
		try {
			$user = $this->userRepository->getByEmail($data->email);

			$this->userManager->passwordForgot($user);

		} catch (NoResultException) {
			$form['email']->addError('nette.message.auth-email-unknown');

		} catch (Throwable $e) {
			$form->addError('web.message.something-went-wrong');
			Debugger::log($e);
		}
	}
}
