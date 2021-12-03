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
	/** @var AdminUserFormFactory */
	private $adminUserFormFactory;

	/** @var UserParamGridFactory */
	private $userParamGridFactory;

	/** @var UserGridFactory */
	private $userGridFactory;

	/** @var UserRepository */
	private $userRepository;

	/** @var User */
	private $user;


	/**
	 * @param UserRepository  $userRepository
	 * @param UserGridFactory  $userGridFactory
	 * @param UserParamGridFactory  $userParamGridFactory
	 * @param AdminUserFormFactory  $adminUserFormFactory
	 */
	public function __construct(
		UserRepository $userRepository,
		UserGridFactory $userGridFactory,
		UserParamGridFactory $userParamGridFactory,
		AdminUserFormFactory $adminUserFormFactory
	) {
		parent::__construct();

		$this->adminUserFormFactory = $adminUserFormFactory;
		$this->userParamGridFactory = $userParamGridFactory;
		$this->userGridFactory = $userGridFactory;
		$this->userRepository = $userRepository;
	}


	/**
	 * @param  int  $id
	 * @return void
	 */
	public function actionEdit(int $id): void
	{
		$this->user = $this->userRepository->getById($id);
	}


	/**
	 * @param  string  $name
	 * @return AdminUserForm
	 */
	protected function createComponentAdminUserForm(string $name): AdminUserForm
	{
		$form = $this->adminUserFormFactory->create($this->user);
		$form->onSuccess[] = function($frm, $data) use ($form) {
	    	if ($frm['apply']->isSubmittedBy()) {
				$this->redirectAjax('edit', ['id' => $form->getUser()->getId()]);
	    	}

			$this->redirectAjax('default');
		};

		$form->onError[] = function() use ($name) {
			if ($this->getAction() == 'default') {
				return;
			}

			$this->openModal($name);
		};

		return $form;
	}


	/**
	 * @param  string  $name
	 * @return UserGrid
	 */
	protected function createComponentUserGrid(string $name): UserGrid
	{
		return $this->userGridFactory->create();
	}


	/**
	 * @param  string  $name
	 * @return UserParamGrid
	 */
	protected function createComponentUserParamGrid(string $name): UserParamGrid
	{
		return $this->userParamGridFactory->create($this->user);
	}
}
