<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2020
 * @license   MIT License
 */

namespace App\DataGrids;

use App\Entity\User;
use App\Entity\UserRepository;
use App\Enums\Active;
use App\Exceptions\PermissionDeniedException;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use JuniWalk\Utils\Enums\Role;
use JuniWalk\Utils\Html;
use JuniWalk\Utils\UI\DataGrids\AbstractGrid;
use Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation;
use Ublaboo\DataGrid\DataGrid;
use Throwable;
use Tracy\Debugger;

final class UserGrid extends AbstractGrid
{
	public function __construct(
		private EntityManager $entityManager,
		private UserRepository $userRepository
	) {
		$this->setTitle('web.control.user-grid');
	}


	public function handleCreate(): void
	{
		$this->getPresenter()->openModal('adminUserForm');
	}


	public function handleRemove(int $id): void
	{
		$presenter = $this->getPresenter();

		try {
			$user = $this->userRepository->getById($id);

			$presenter->isAllowed('Admin:User', 'remove', $user->getRole());

            $this->entityManager->remove($user);
			$this->entityManager->flush();

		} catch (PermissionDeniedException) {
			$presenter->flashMessage('web.message.permission-denied', 'warning');

		} catch (Throwable $e) {
			$presenter->flashMessage('web.message.something-went-wrong', 'danger');
			Debugger::log($e);
		}

		$presenter->redirectAjax('this');
		$this->redrawGrid();
	}


	public function handleRole(int $id, Role $role): void
	{
		$presenter = $this->getPresenter();

		try {
			$user = $this->userRepository->getReference($id);
			$user->setRole($role);

			$presenter->isAllowed('Admin:User', 'edit.role', $user->getRole());

			$this->entityManager->flush();

		} catch (PermissionDeniedException) {
			$presenter->flashMessage('web.message.permission-denied', 'warning');

		} catch (Throwable $e) {
			$presenter->flashMessage('web.message.something-went-wrong', 'danger');
			Debugger::log($e);
		}

		$presenter->redirectAjax('this');
		$this->redrawItem($id);
	}


	public function handleActive(int $id): void
	{
		$presenter = $this->getPresenter();

		try {
			$user = $this->userRepository->getById($id);
			$user->setActive(!$user->isActive());

			$presenter->isAllowed('Admin:User', 'edit.active', $user->getRole());

			$this->entityManager->flush();

		} catch (PermissionDeniedException) {
			$presenter->flashMessage('web.message.permission-denied', 'warning');

		} catch (Throwable $e) {
			$presenter->flashMessage('web.message.something-went-wrong', 'danger');
			Debugger::log($e);
		}

		$presenter->redirectAjax('this');
		$this->redrawItem($id);
	}


	protected function createModel(): mixed
	{
		return $this->userRepository->createQueryBuilder('e', 'e.id');
	}


	protected function createComponentGrid(): DataGrid
	{
		$grid = $this->createDataGrid();
		$grid->setDefaultSort([
			'name' => 'ASC',
		]);

		$grid->addColumnText('name', 'web.user.name')->setSortable()->setRenderer($this->columnName(...));
		$this->addColumnEnum('role', 'web.user.role', Role::class)->setSortable('e.role');
		$grid->addColumnText('email', 'web.user.email')->setSortable();
		$grid->addColumnDateTime('signUp', 'web.user.signUp')->setSortable();
		$grid->addColumnDateTime('signIn', 'web.user.signIn')->setSortable();
		$grid->addColumnText('isActive', 'web.general.active')->setSortable()->setAlign('right')->setRenderer($this->columnActive(...));


		$grid->addFilterText('name', 'web.user.name')->setCondition(function($qb, $value) {
			$qb->andWhere('LOWER(e.name) LIKE LOWER(:name)')->setParameter('name', '%'.$value.'%');
		});
		$grid->addFilterText('email', 'web.user.email')->setCondition(function($qb, $value) {
			$qb->andWhere('LOWER(e.email) LIKE LOWER(:email)')->setParameter('email', '%'.$value.'%');
		});
		$grid->addFilterMultiSelect('role', 'web.user.role', Role::getLabels())
			->setTranslateOptions(true);
		$grid->addFilterSelect('isActive', 'web.general.active', Active::getLabels())
			->setTranslateOptions(true);


		$grid->addToolbarButton('create!', 'web.general.create')->setIcon('plus')
			->setClass('btn btn-sm btn-success ajax');

		$grid->addAction('edit', '', ':Admin:User:edit')->setIcon('pencil-alt')
			->setClass('btn btn-primary btn-xs')
			->setTitle('web.general.edit');

		$grid->addAction('remove!', 'web.general.remove')->setIcon('trash-alt')
			->setConfirmation(new StringConfirmation('web.message.confirm-deletion', 'name'))
			->setClass('btn btn-danger btn-xs ajax')
			->setTitle('web.general.remove');

		return $grid;
	}


	private function columnName(User $user): Html
	{
		$presenter = $this->getPresenter();
		$link = $presenter->lazyLink('User:edit', $user->getId());
		$name = Html::el('a', $user->getDisplayName())->setHref($link);

		if (!$user->isActive()) {
			$icon = Html::icon('fa-ban float-right mt-1');
			$name->addClass('font-weight-bolder text-danger');
			$name->addText(' ')->addHtml($icon);
		}

		return $name;
	}


	private function columnActive(User $user): ?Html
	{
		$link = $this->lazyLink('active!', $user->getId());
		return Html::el('a class="ajax"')->setHref($link)
			->addHtml(Html::status($user->isActive()));
	}
}
