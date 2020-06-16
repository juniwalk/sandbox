<?php declare(strict_types=1);

/**
 * @copyright Design Point, s.r.o. (c) 2016
 * @license   MIT License
 */

namespace App\DataGrids;

use Nette\Application\UI\Control;
use Nette\Localization\ITranslator;
use Ublaboo\DataGrid\DataGrid;

/**
 * @method void onBeforeRender(self $self, ITemplate $template)
 */
abstract class AbstractGrid extends Control
{
	/** @var bool */
	private $isDisabled = false;

	/** @var bool */
	private $isTableResponsive = true;

	/** @var string */
	private $title;

	/** @var ITranslator */
	private $translator;

	/** @var callable[] */
	public $onBeforeRender = [];


	/**
	 * @param  ITranslator|null  $translator
	 * @return void
	 */
	public function setTranslator(ITranslator $translator = null): void
	{
		$this->translator = $translator;
	}


	/**
	 * @return ITranslator|null
	 */
	public function getTranslator(): ?ITranslator
	{
		return $this->translator;
	}


	/**
	 * @param  string  $title
	 * @return void
	 */
	public function setTitle(string $title): void
	{
		$this->title = $title;
	}


	/**
	 * @return string|null
	 */
	public function getTitle(): ?string
	{
		return $this->title;
	}


	/**
	 * @param  bool  $disabled
	 * @return void
	 */
	public function setDisabled(bool $disabled = true): void
	{
		$this->isDisabled = $disabled;
	}


	/**
	 * @param  bool  $tableResponsive
	 * @return void
	 */
	public function setTableResponsive(bool $tableResponsive): void
	{
		$this->isTableResponsive = $tableResponsive;
	}


	/**
	 * @return bool
	 */
	public function isDisabled(): bool
	{
		return $this->isDisabled;
	}


	/**
	 * @return bool
	 */
	public function isTableResponsive(): bool
	{
		return $this->isTableResponsive;
	}


	final public function render()
	{
		$this->getComponent('grid')->getTemplate()
			->add('isTableResponsive', $this->isTableResponsive)
			->add('controlName', $this->getName())
			->add('isDisabled', $this->isDisabled)
			->add('title', $this->title);

		$template = $this->getTemplate();
		$template->setFile(__DIR__.'/templates/datagrid-wrapper.latte');

		if (!empty($this->onBeforeRender)) {
			$this->onBeforeRender($this, $template);
		}

		$template->render();
	}


	/**
	 * @return void
	 */
	final public function redrawGrid(): void
	{
		$this['grid']->redrawControl();
	}


	/**
	 * @param  int  $id
	 * @return void
	 */
	final public function redrawItem(int $id): void
	{
		$this['grid']->redrawItem($id);
	}


	/**
	 * @param  string  $name
	 * @return DataGrid
	 */
	abstract protected function createComponentGrid(string $name): DataGrid;


	/**
	 * @return Doctrine\ORM\QueryBuilder
	 */
	protected function createModel()
	{
		return [];
	}


	/**
	 * @param  string  $name
	 * @param  bool  $rememberState
	 * @return DataGrid
	 */
	final protected function createGrid(string $name, bool $rememberState = true): DataGrid
	{
		$grid = new DataGrid(null, $name);
		$grid->setItemsPerPageList([50, 100, 200]);
		$grid->setDefaultPerPage(50);
		$grid->setTemplateFile(__DIR__.'/templates/datagrid.latte');
		$grid->setDataSource($this->createModel());
		$grid->setRememberState($rememberState);
		$grid->setOuterFilterRendering(true);
		$grid->setMultiSortEnabled(true);

		if ($this->translator instanceof ITranslator) {
			$grid->setTranslator($this->translator);
		}

		DataGrid::$iconPrefix = 'fas fa-';

		return $grid;
	}
}
