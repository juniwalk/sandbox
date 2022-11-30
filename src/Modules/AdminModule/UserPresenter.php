<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2020
 * @license   MIT License
 */

namespace App\Modules\AdminModule;

use App\DataGrids\Factory\UserGridFactory;
use App\DataGrids\Factory\UserParamGridFactory;
use App\DataGrids\UserGrid;
use App\DataGrids\UserParamGrid;
use App\Entity\User;
use App\Entity\UserRepository;
use App\Forms\Factory\AdminUserFormFactory;
use App\Forms\AdminUserForm;
use App\Modules\AbstractPresenter;

final class UserPresenter extends AbstractPresenter
{
	private ?User $user = null;


	public function __construct(
		private UserRepository $userRepository,
		private UserGridFactory $userGridFactory,
		private UserParamGridFactory $userParamGridFactory,
		private AdminUserFormFactory $adminUserFormFactory
	) {}


	public function actionEdit(int $id): void
	{
		$this->user = $this->userRepository->getById($id);
	}


	protected function createComponentAdminUserForm(string $name): AdminUserForm
	{
		$form = $this->adminUserFormFactory->create($this->user);
		$form->onSuccess[] = function() use ($form) {
			$this->redirectAjax($form->findRedirectPage(['apply' => 'edit']), [
				'id' => $form->getUser()?->getId(),
			]);

			$this['userGrid']->redrawGrid();
			$this->redrawControl('modals');
		};

		$form->onError[] = function() use ($name) {
			$this->isAjax() && $this->openModal($name);
		};

		return $form;
	}


	protected function createComponentUserGrid(): UserGrid
	{
		return $this->userGridFactory->create();
	}


	protected function createComponentUserParamGrid(): UserParamGrid
	{
		return $this->userParamGridFactory->create($this->user);
	}
}
