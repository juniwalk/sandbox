<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2020
 * @license   MIT License
 */

namespace App\DataGrids;

use App\Entity\Parameter;
use App\Entity\ParameterRepository;
use App\Entity\User;
use Nette\Utils\DateTime;
use Nette\Utils\Html;
use Nette\Utils\Strings;
use Ublaboo\DataGrid\DataGrid;

final class UserParamGrid extends AbstractGrid
{
	/** @var ParameterRepository */
	private $parameterRepository;

	/** @var User */
	private $user;


	/**
	 * @param User  $user
	 * @param ParameterRepository  $parameterRepository
	 */
	public function __construct(
		User $user,
		ParameterRepository $parameterRepository
	) {
		$this->parameterRepository = $parameterRepository;
		$this->user = $user;

		$this->setTitle('web.control.userparam-grid');
		$this->setFiltersAlwaysShown(true);
	}


	/**
	 * @return iterable
	 */
	protected function createModel()//: iterable
	{
		return $this->parameterRepository->createQueryBuilder('e')
			->where('e.user = :user')
			->setParameter('user', $this->user);
	}


	/**
	 * @param  string  $name
	 * @return DataGrid
	 */
	protected function createComponentGrid(string $name): DataGrid
	{
		$grid = $this->createGrid($name, true, 'key');
		$grid->setDefaultSort(['key' => 'ASC']);
		$grid->setPagination(false);

		$grid->addColumnText('key', 'web.param.key')->setRenderer([$this, 'columnKey']);
		$grid->addColumnText('value', 'web.param.value')->setRenderer([$this, 'columnValue']);
		$grid->addColumnDateTime('created', 'web.general.created')->setFormat('j. n. Y G:i');
		$grid->addColumnDateTime('modified', 'web.general.modified')->setFormat('j. n. Y G:i');

		return $grid;
	}


	/**
	 * @param  Parameter  $param
	 * @return string
	 */
	public function columnKey(Parameter $param): string
	{
		$translator = $this->getTranslator();

		$key = 'web.enum.param.'.$param->getKey();
		$key = $translator->translate($key);

		return $key;
	}


	/**
	 * @param  Parameter  $param
	 * @return mixed
	 */
	public function columnValue(Parameter $param)
	{
		$translator = $this->getTranslator();
		$value = $param->getValue();

		switch ($param->getKey()) {
			default: break;
		}

		return $value;
	}
}
