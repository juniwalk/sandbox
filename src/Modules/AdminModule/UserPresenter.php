<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2020
 * @license   MIT License
 */

namespace App\Modules\AdminModule;

use App\DataGrids\Factory\UserGridFactory;
use App\Entity\User;
use App\Entity\UserRepository;
use App\Forms\Factory\UserFormFactory;
use App\Modules\AbstractPresenter;

final class UserPresenter extends AbstractPresenter
{
	/** @var UserFormFactory */
	private $userFormFactory;

	/** @var UserGridFactory */
	private $userGridFactory;

	/** @var UserRepository */
	private $userRepository;

	/** @var User */
	private $user;


	/**
	 * @param UserRepository  $userRepository
	 * @param UserGridFactory  $userGridFactory
	 * @param UserFormFactory  $userFormFactory
	 */
	public function __construct(
		UserRepository $userRepository,
		UserGridFactory $userGridFactory,
		UserFormFactory $userFormFactory
	) {
		parent::__construct();

		$this->userRepository = $userRepository;
		$this->userGridFactory = $userGridFactory;
		$this->userFormFactory = $userFormFactory;
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
	protected function createComponentUserGrid(string $name)
	{
		return $this->userGridFactory->create();
	}
}
