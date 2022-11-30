<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2020
 * @license   MIT License
 */

namespace App\DataGrids;

use App\Entity\Parameter;
use App\Entity\ParameterRepository;
use App\Entity\User;
use Ublaboo\DataGrid\DataGrid;
use JuniWalk\Utils\Strings;
use JuniWalk\Utils\UI\DataGrids\AbstractGrid;

final class UserParamGrid extends AbstractGrid
{
	public function __construct(
		private readonly User $user,
		private readonly ParameterRepository $parameterRepository,
	) {
		$this->setTitle('web.control.userparam-grid');
	}


	protected function createModel(): mixed
	{
		return $this->parameterRepository->createQueryBuilder('e')
			->where('e.user = :user')
			->setParameter('user', $this->user);
	}


	protected function createComponentGrid(): DataGrid
	{
		$grid = $this->createDataGrid(primaryKey: 'key');
		$grid->setDefaultSort(['key' => 'ASC']);
		$grid->setPagination(false);

		$grid->addColumnText('key', 'web.param.key')->setRenderer($this->columnKey(...));
		$grid->addColumnText('value', 'web.param.value')->setRenderer($this->columnValue(...));
		$grid->addColumnDateTime('created', 'web.general.created')->setFormat('j. n. Y G:i');
		$grid->addColumnDateTime('modified', 'web.general.modified')->setFormat('j. n. Y G:i');

		return $grid;
	}


	protected function columnKey(Parameter $param): string
	{
		$key = 'web.enum.param.'.Strings::webalize($param->getKey());
		return $this->getTranslator()->translate($key);
	}


	protected function columnValue(Parameter $param): mixed
	{
		$translator = $this->getTranslator();
		$value = $param->getValue();

		switch ($param->getKey()) {
			default: break;
		}

		return $value;
	}
}
