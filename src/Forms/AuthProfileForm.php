<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2020
 * @license   MIT License
 */

namespace App\Forms;

use App\Entity\User;
use App\Managers\MessageManager;
use App\Managers\UserManager;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use JuniWalk\Form\AbstractForm;

final class AuthProfileForm extends AbstractForm
{
	public function __construct(
		private User $user,
		private UserManager $userManager,
		private EntityManager $entityManager,
		private MessageManager $messageManager
	) {
		$this->onBeforeRender[] = function($form, $template) {
			$template->add('profile', $this->user);
			$this->setDefaults($this->user);
		};
	}


	public function setDefaults(User $user): void
	{
		$form = $this->getForm();
		$form->setDefaults([
			'name' => $user->getName(),
			'email' => $user->getEmail(),
		]);
	}


	public function getUser(): User
	{
		return $this->user;
	}


	public function handleResendActivationEmail(): void
	{
		$presenter = $this->getPresenter();
		$user = $this->getUser();

		if ($user->isEmailActivated()) {
			$this->redirectAjax('this');
		}

		try {
			// $presenter->isAllowed('Admin:User', 'edit.password', $user->getRole());

			$this->userManager->deactivateUser($user);
			$this->entityManager->flush();

			$this->messageManager->sendUserSignUpMessage($user);
			$presenter->flashMessage('web.message.auth-email-sent', 'success');

		} catch (PermissionDeniedException) {
			$presenter->flashMessage('web.message.permission-denied', 'warning');

		} catch (Throwable $e) {
			$presenter->flashMessage('web.message.something-went-wrong', 'danger');
			Debugger::log($e);
		}

		$presenter->redirectAjax('this');
		$this->redrawControl('form');
	}


	protected function createComponentForm(string $name): Form
	{
		$form = parent::createComponentForm($name);
		$form->addText('name');
		$form->addText('email')->setRequired('nette.user.email-required')->setType('email');
		$form->addPassword('password')->addCondition($form::FILLED)
			->addRule($form::MIN_LENGTH, 'nette.user.password-length', 6);

		$form->addSubmit('submit');

		return $form;
	}


	protected function handleSuccess(Form $form, ArrayHash $data): void
	{
		$user = $this->getUser();
		$user->setName($data->name);
		$user->setEmail($data->email);

		if (!empty($data->password)) {
			$user->setPassword($data->password);
		}

		try {
			$this->entityManager->flush();

		} catch(UniqueConstraintViolationException) {
			$form['email']->addError('web.message.auth-email-used');

		} catch (Throwable $e) {
			$form->addError('web.message.something-went-wrong');
			Debugger::log($e);
		}
	}
}
