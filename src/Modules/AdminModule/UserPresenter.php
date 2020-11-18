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
use App\Forms\Factory\UserFormFactory;
use App\Modules\AbstractPresenter;

final class UserPresenter extends AbstractPresenter
{
	/** @var UserParamGridFactory */
	private $userParamGridFactory;

	/** @var UserGridFactory */
	private $userGridFactory;

	/** @var UserFormFactory */
	private $userFormFactory;

	/** @var UserRepository */
	private $userRepository;

	/** @var User */
	private $user;


	/**
	 * @param UserRepository  $userRepository
	 * @param UserFormFactory  $userFormFactory
	 * @param UserGridFactory  $userGridFactory
	 * @param UserParamGridFactory  $userParamGridFactory
	 */
	public function __construct(
		UserRepository $userRepository,
		UserFormFactory $userFormFactory,
		UserGridFactory $userGridFactory,
		UserParamGridFactory $userParamGridFactory
	) {
		parent::__construct();

		$this->userParamGridFactory = $userParamGridFactory;
		$this->userGridFactory = $userGridFactory;
		$this->userFormFactory = $userFormFactory;
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
	 * @return UserForm
	 */
	protected function createComponentUserForm(string $name)
	{
		$form = $this->userFormFactory->create($this->user);
		$form->onSuccess[] = function ($frm, $data) use ($form) {
	    	if ($frm['apply']->isSubmittedBy()) {
				$this->redirect('edit', ['id' => $form->getUser()->getId()]);
	    	}

			$this->redirect('default');
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
