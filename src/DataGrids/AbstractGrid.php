<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2020
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
	private $isResponsive = true;

	/** @var bool */
	private $hasFiltersAlwaysShown = false;

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
	 * @return bool
	 */
	public function isDisabled(): bool
	{
		return $this->isDisabled;
	}


	/**
	 * @param  bool  $responsive
	 * @return void
	 */
	public function setResponsive(bool $responsive = true): void
	{
		$this->isResponsive = $responsive;
	}


	/**
	 * @return bool
	 */
	public function isResponsive(): bool
	{
		return $this->isResponsive;
	}


	/**
	 * @param  bool  $disabled
	 * @return void
	 */
	public function setFiltersAlwaysShown(bool $filtersAlwaysShown = true): void
	{
		$this->hasFiltersAlwaysShown = $filtersAlwaysShown;
	}


	/**
	 * @return bool
	 */
	public function hasFiltersAlwaysShown(): bool
	{
		return $this->hasFiltersAlwaysShown;
	}


	final public function render()
	{
		$gridTemplate = $this->getComponent('grid')->getTemplate();
		$gridTemplate->controlName = $this->getName();
		$gridTemplate->hasFiltersAlwaysShown = $this->hasFiltersAlwaysShown;
		$gridTemplate->isResponsive = $this->isResponsive;
		$gridTemplate->isDisabled = $this->isDisabled;
		$gridTemplate->title = $this->title;

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
	final protected function createGrid(string $name, bool $rememberState = true, string $primaryKey = null): DataGrid
	{
		$grid = new DataGrid(null, $name);
		$grid->setRememberState($rememberState);
		$grid->setRefreshUrl(!$rememberState);
		$grid->setCustomPaginatorTemplate(__DIR__.'/templates/datagrid_paginator.latte');
		$grid->setTemplateFile(__DIR__.'/templates/datagrid.latte');
		$grid->setItemsPerPageList([10, 20, 50], false);
		$grid->setDefaultPerPage(20);

		if (isset($primaryKey)) {
			$grid->setPrimaryKey($primaryKey);
		}

		$grid->setDataSource($this->createModel());
		$grid->setStrictSessionFilterValues(false);
		$grid->setOuterFilterRendering(true);

		if ($this->translator instanceof ITranslator) {
			$grid->setTranslator($this->translator);
		}

		DataGrid::$iconPrefix = 'fas fa-fw fa-';

		return $grid;
	}


	/**
	 * @return string[]
	 */
	protected function createActiveOptions(): iterable
	{
		return [
			null => 'web.general.all',
			1 => 'web.general.yes',
			0 => 'web.general.no',
		];
	}
}
