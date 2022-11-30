<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2021
 * @license   MIT License
 */

namespace App\Forms;

use JuniWalk\Form\AbstractForm;
use Doctrine\ORM\NoResultException;
use Nette\Application\UI\Form;
use Nette\Security\AuthenticationException;
use Nette\Security\User;
use Nette\Utils\ArrayHash;

final class AuthSignInForm extends AbstractForm
{
	public function __construct(
		private User $user
	) {}


	protected function createComponentForm(string $name): Form
	{
		$form = parent::createComponentForm($name);
		$form->addText('email')->setRequired('web.user.email-required')
			->addRule($form::EMAIL, 'web.user.email-invalid');
		$form->addPassword('password')->setRequired('web.user.password-required')
			->addRule($form::MIN_LENGTH, 'web.user.password-length', 6);

		$form->addSubmit('submit');

		return $form;
	}


	protected function handleSuccess(Form $form, ArrayHash $data): void
	{
		try {
			$this->user->login($data->email, $data->password);

		} catch (NoResultException) {
			$form->addError('web.message.auth-invalid');

		} catch (AuthenticationException $e) {
			$form->addError($e->getMessage());
		}
	}
}
