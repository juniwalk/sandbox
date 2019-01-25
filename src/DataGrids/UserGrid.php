<?php declare(strict_types=1);

/**
 * @copyright Design Point, s.r.o. (c) 2016
 * @license   MIT License
 */

namespace App\DataGrids;

use App\Entity\User;
use App\Entity\UserRepository;
use App\Entity\Enums\Role;
use App\Tools\HtmlHelper;
use Carrooi\ImagesManager\ImagesManager;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use Doctrine\ORM\ORMException;
use Nette\Utils\Html;
use Ublaboo\DataGrid\DataGrid;

final class UserGrid extends AbstractGrid
{
	/** @var UserRepository */
	private $userRepository;

	/** @var ImagesManager */
	private $imagesManager;

	/** @var EntityManager */
	private $entityManager;


	/**
	 * @param EntityManager  $entityManager
 	 * @param ImagesManager  $imagesManager
	 * @param UserRepository  $userRepository
	 */
	public function __construct(
		EntityManager $entityManager,
        ImagesManager $imagesManager,
		UserRepository $userRepository
	) {
		$this->userRepository = $userRepository;
		$this->imagesManager = $imagesManager;
		$this->entityManager = $entityManager;

		$this->setTitle('nette.page.admin-user-default');
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

            $this->entityManager->remove($user);
            $this->entityManager->flush();

        } catch (ORMException $e) {
        }

		$this->redrawGrid();
	}


	/**
	 * @param  int  $id
	 * @return void
	 * @secured
	 */
	public function handleActive(int $id): void
	{
        try {
            $user = $this->userRepository->getById($id);
			$user->setActive(!$user->isActive());

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

		$grid->addColumnText('name', 'nette.user.name')->setSortable()
            ->setRenderer([$this, 'columnName']);
		$grid->addColumnText('role', 'nette.user.role')->setSortable()
            ->setRenderer([$this, 'columnRole']);
		$grid->addColumnText('email', 'nette.user.email')->setSortable();
		$grid->addColumnText('isActive', 'nette.user.active')->setSortable()->setAlign('right')
			->setRenderer([$this, 'columnActive']);


		$grid->addFilterText('name', 'nette.user.name')->setCondition(function ($qb, $value) {
			$qb->andWhere('LOWER(e.name) LIKE LOWER(:name)')->setParameter('name', '%'.$value.'%');
		});
		$grid->addFilterText('email', 'nette.user.email')->setCondition(function ($qb, $value) {
			$qb->andWhere('LOWER(e.email) LIKE LOWER(:email)')->setParameter('email', '%'.$value.'%');
		});
		$grid->addFilterSelect('role', 'nette.user.role', [NULL => 'nette.general.all'] + (new Role)->getItems())
			->setTranslateOptions(TRUE);
		$grid->addFilterSelect('isActive', 'nette.user.active', $this->createActiveOptions())
			->setTranslateOptions(TRUE);


        $grid->addToolbarButton('User:create', 'nette.general.create')
            ->setClass('btn btn-success btn-sm')->setIcon('plus');

		$grid->addAction('User:edit', NULL)->setIcon('pencil-alt')
			->setClass('btn btn-primary btn-xs')
			->setTitle('nette.general.edit');

		$grid->addAction('remove!', NULL)->setIcon('trash-alt')
			->setConfirm('nette.message.confirm-deletion', 'name')
			->setClass('btn btn-danger btn-xs ajax')
			->setTitle('nette.general.remove');

		return $grid;
	}


	/**
	 * @param  User  $user
	 * @return Html
	 */
	public function columnName(User $user): Html
	{
		$presenter = $this->getPresenter();

		$image = $this->imagesManager->findImage('avatar', $user);
		$this->imagesManager->tryStoreThumbnail($image, 64, 64, 0);
		$avatar = Html::el('img class="user-image" alt="'.$user.'"')
			->setSrc($this->imagesManager->getUrl($image, 64, 64));

		$link = $presenter->lazyLink(':Admin:User:edit', $user->getId());
		$name = Html::el('a')->setHref($link)->addHtml($avatar)
			->addText($user->getName());

		if (!$user->isActive()) {
			$icon = Html::el('i class="fas fa-ban pull-right"');
			$name->addClass('text-700 text-danger');
			$name->addText(' ')->addHtml($icon);
		}

		return $name;
	}


	/**
	 * @param  User  $user
	 * @return Html|NULL
	 */
	public function columnRole(User $user): ?Html
	{
        if (!$role = $user->getRole()) {
            return NULL;
        }

        $role = (new Role)->getItem($role);

		return HtmlHelper::createLabel($role);
	}


	/**
	 * @param  User  $user
	 * @return Html|NULL
	 */
	public function columnActive(User $user): ?Html
	{
		$status = HtmlHelper::createStatus($user->isActive());
		$link = $this->lazyLink('active!', $user->getId());
		$html = Html::el('a class="ajax"')->setHref($link)
			->addHtml($status);

		return $html;
	}


	/**
	 * @return string[]
	 */
	private function createActiveOptions(): iterable
	{
		return [
			NULL => 'nette.general.all',
			1 => 'nette.general.yes',
			0 => 'nette.general.no',
		];
	}
}
