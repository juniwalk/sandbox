<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2020
 * @license   MIT License
 */

namespace App\Forms;

use App\Entity\User;
use App\Exceptions\PermissionDeniedException;
use App\Managers\UserManager;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use JuniWalk\Form\AbstractForm;
use JuniWalk\Utils\Enums\Role;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use Throwable;
use Tracy\Debugger;

final class AdminUserForm extends AbstractForm
{
	public function __construct(
		private ?User $user,
		private UserManager $userManager,
		private EntityManager $entityManager,
	) {
		$this->onBeforeRender[] = function($self, $tpl) {
			$tpl->add('profile', $this->user);
			$this->setDefaults($this->user);
		};
	}


	public function setDefaults(?User $user): void
	{
		if (!isset($user)) {
			return;
		}

		$form = $this->getForm();
		$form->setDefaults([
			'name' => $user->getName(),
			'email' => $user->getEmail(),
			'role' => $user->getRole(),
			'active' => $user->isActive(),
		]);
	}


	public function getUser(): ?User
	{
		return $this->user;
	}


	public function handlePasswordForgot(): void
	{
		$presenter = $this->getPresenter();
		$user = $this->getUser();

		try {
			$presenter->isAllowed('Admin:User', 'edit.password', $user->getRole());

			$this->userManager->passwordForgot($user);

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
		$form->addText('email')->setHtmlType('email')
			->setRequired('web.user.email-required');
			$form->addSelectEnum('role', null, Role::cases())
				->setRequired('web.user.role-required');
		$form->addCheckbox('isActive')->setRequired(false);

		$form->addSubmit('submit');
		$form->addSubmit('apply');

		return $form->setDefaults([
			'role' => Role::User,
			'isActive' => true,
		]);
	}


	protected function handleSuccess(Form $form, ArrayHash $data): void
	{
		$presenter = $this->getPresenter();

		$user = $this->getUser() ?: new User($data->email, $data->name);
		$user->setName($data->name);
		$user->setEmail($data->email);
		$user->setRole($data->role);
		$user->setActive($data->isActive);

		try {
			$presenter->isAllowed('Admin:User', 'edit', $data->role);

			$this->entityManager->persist($user);
			$this->entityManager->flush();
			$this->user = $user;

		} catch (UniqueConstraintViolationException) {
			$form['email']->addError('web.message.user-already-exists');

		} catch (PermissionDeniedException) {
			$presenter->flashMessage('web.message.permission-denied', 'warning');

		} catch (Throwable $e) {
			$form->addError('web.message.something-went-wrong');
			Debugger::log($e);
		}
	}
}
