<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2020
 * @license   MIT License
 */

namespace App\DataGrids;

use App\Entity\User;
use App\Entity\UserRepository;
use App\Entity\Enums\Role;
use App\Tools\HtmlHelper;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use Doctrine\ORM\ORMException;
use Nette\Utils\ArrayHash;
use Nette\Utils\Html;
use Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation;
use Ublaboo\DataGrid\DataGrid;

final class UserGrid extends AbstractGrid
{
	/** @var UserRepository */
	private $userRepository;

	/** @var EntityManager */
	private $entityManager;


	/**
	 * @param EntityManager  $entityManager
	 * @param UserRepository  $userRepository
	 */
	public function __construct(
		EntityManager $entityManager,
		UserRepository $userRepository
	) {
		$this->userRepository = $userRepository;
		$this->entityManager = $entityManager;

		$this->setTitle('web.control.user-grid');
	}


	/**
	 * @param  int  $id
	 * @return void
	 * @secured
	 */
	public function handleRemove(int $id): void
	{
        try {
            $user = $this->userRepository->getById($id);

			if ($image = $user->getImage()) {
				$this->imageStorage->delete($image);
			}

            $this->entityManager->remove($user);
            $this->entityManager->flush();

        } catch (ORMException $e) {
        }

		$this->redrawGrid();
	}


	/**
	 * @param  int  $id
	 * @param  string  $role
	 * @return void
	 * @secured
	 */
	public function handleRole(int $id, string $role): void
	{
        try {
            $user = $this->userRepository->getById($id);
			$user->setRole($role);

            $this->entityManager->flush();

        } catch (ORMException $e) {
        }

		$this->redrawGrid();
	}


	/**
	 * @param  int  $id
	 * @param  bool  $value
	 * @return void
	 * @secured
	 */
	public function handleActive(int $id, bool $value): void
	{
        try {
            $user = $this->userRepository->getById($id);
			$user->setActive($value);

            $this->entityManager->flush();

        } catch (ORMException $e) {
        }

		$this->redrawGrid();
	}


	/**
	 * @return iterable
	 */
	protected function createModel()//: iterable
	{
		return $this->userRepository->createQueryBuilder('e', 'e.id');
	}


	/**
	 * @param  string  $name
	 * @return DataGrid
	 */
	protected function createComponentGrid(string $name): DataGrid
	{
		$grid = $this->createGrid($name);
		$grid->setDefaultSort(['name' => 'ASC']);

		$grid->addColumnText('name', 'web.user.name')->setSortable()
			->setRenderer([$this, 'columnName']);
		$roleStatus = $grid->addColumnStatus('role', 'web.user.role')->setSortable();
		$roleStatus->onChange[] = function($id, $value) { $this->handleRole((int) $id, $value); };

		foreach ((new Role)->getItems() as $key => $role) {
			$color = (new Role)->getColor($key);
			$roleStatus->addOption($key, $role)
				->setClass('btn-'.$color)
				->endOption();
		}

		$grid->addColumnText('email', 'web.user.email')->setSortable();
		$activeStatus = $grid->addColumnStatus('isActive', 'web.user.active')->setSortable()->setAlign('right');
		$activeStatus->onChange[] = function($id, $value) { $this->handleActive((int) $id, (bool) $value); };
		$activeStatus->addOption(true, 'web.general.yes')
				->setIconSecondary('fas fa-check fa-fw')
				->setIcon('fas fa-check fa-fw')
				->setClass('btn-success')
				->endOption()
			->addOption(false, 'web.general.no')
				->setIconSecondary('fas fa-times fa-fw')
				->setIcon('fas fa-times fa-fw')
				->setClass('btn-danger')
				->endOption();

		$grid->addColumnDateTime('signUp', 'web.user.signUp')->setSortable()->setFormat('j. n. Y G:i');
		$grid->addColumnDateTime('signIn', 'web.user.signIn')->setSortable()->setFormat('j. n. Y G:i');
		$grid->addColumnNumber('id', 'web.general.id')->setSortable()->setFormat(0, ',', '');


		$grid->addFilterText('name', 'web.user.name')->setCondition(function ($qb, $value) {
			$qb->andWhere('LOWER(e.name) LIKE LOWER(:name)')->setParameter('name', '%'.$value.'%');
		});
		$grid->addFilterText('email', 'web.user.email')->setCondition(function ($qb, $value) {
			$qb->andWhere('LOWER(e.email) LIKE LOWER(:email)')->setParameter('email', '%'.$value.'%');
		});
		$grid->addFilterMultiSelect('role', 'web.user.role', [null => 'web.general.all'] + (new Role)->getItems())
			->setTranslateOptions(true);
		$grid->addFilterSelect('isActive', 'web.user.active', $this->createActiveOptions())
			->setTranslateOptions(true);


        $grid->addToolbarButton('User:create', 'web.general.create')
            ->setClass('btn btn-success btn-sm')->setIcon('plus');

		$grid->addAction('User:edit', 'web.general.edit')->setIcon('pencil-alt')
			->setClass('btn btn-primary btn-xs')
			->setTitle('web.general.edit');

		$grid->addAction('remove!', 'web.general.remove')->setIcon('trash-alt')
			->setConfirmation(new StringConfirmation('web.message.confirm-deletion', 'name'))
			->setClass('btn btn-danger btn-xs ajax')
			->setTitle('web.general.remove');

		return $grid;
	}


	/**
	 * @param  User  $user
	 * @return Html
	 */
	public function columnName(User $user): Html
	{
		$presenter = $this->getPresenter();
		$basePath = $presenter->getHttpRequest()->getUrl()->getBasePath();

		$link = $presenter->lazyLink('User:edit', $user->getId());
		$name = Html::el('a', $user->getDisplayName())->setHref($link);

		if (!$user->isActive()) {
			$icon = Html::el('i class="fas fa-ban pull-right"');
			$name->addClass('text-700 text-danger');
			$name->addText(' ')->addHtml($icon);
		}

		return $name;
	}


	/**
	 * @param  User  $user
	 * @return Html|null
	 */
	public function columnRole(User $user): ?Html
	{
        if (!$role = $user->getRole()) {
            return null;
        }

        $role = (new Role)->getItem($role);

		return HtmlHelper::createLabel($role);
	}


	/**
	 * @return string[]
	 */
	private function createActiveOptions(): iterable
	{
		return [
			null => 'web.general.all',
			1 => 'web.general.yes',
			0 => 'web.general.no',
		];
	}
}
