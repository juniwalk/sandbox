<?php declare(strict_types=1);

/**
 * @copyright Design Point, s.r.o. (c) 2016
 * @license   MIT License
 */

namespace App\DataGrids;

use Nette\Application\UI\Control;
use Nette\Localization\ITranslator;
use Nextras\Application\UI\SecuredLinksControlTrait;
use Ublaboo\DataGrid\DataGrid;

/**
 * @method void onBeforeRender(self $self, ITemplate $template)
 */
abstract class AbstractGrid extends Control
{
	//use SecuredLinksControlTrait;


	/**
	 * @var bool
	 */
	private $isDisabled = FALSE;

	/**
	 * @var bool
	 */
	private $isTableResponsive = TRUE;

	/**
	 * @var string
	 */
	private $title;

	/**
	 * @var ITranslator
	 */
	private $translator;

	/**
	 * @var callable[]
	 */
	public $onBeforeRender = [];


	/**
	 * @param ITranslator|NULL  $translator
	 */
	public function setTranslator(ITranslator $translator = NULL)
	{
		$this->translator = $translator;
	}


	/**
	 * @return ITranslator|NULL
	 */
	public function getTranslator(): ?ITranslator
	{
		return $this->translator;
	}


	/**
	 * @param  string  $title
	 */
	public function setTitle(string $title)
	{
		$this->title = $title;
	}


	/**
	 * @return string
	 */
	public function getTitle(): ?string
	{
		return $this->title;
	}


	/**
	 * @param bool  $disabled
	 */
	public function setDisabled(bool $disabled = TRUE)
	{
		$this->isDisabled = $disabled;
	}


	/**
	 * @param bool  $tableResponsive
	 */
	public function setTableResponsive(bool $tableResponsive)
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
	 * @return NULL
	 */
	final public function redrawGrid()
	{
		return $this['grid']->redrawControl();
	}


	/**
	 * @param  int  $id
	 * @return NULL
	 */
	final public function redrawItem(int $id)
	{
		return $this['grid']->redrawItem($id);
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
	final protected function createGrid(string $name, bool $rememberState = TRUE): DataGrid
	{
		$grid = new DataGrid(NULL, $name);
		$grid->setItemsPerPageList([50, 100, 200]);
		$grid->setDefaultPerPage(50);
		$grid->setTemplateFile(__DIR__.'/templates/datagrid-adminlte.latte');
		$grid->setDataSource($this->createModel());
		$grid->setRememberState($rememberState);
		$grid->setOuterFilterRendering(TRUE);
		$grid->setMultiSortEnabled(TRUE);

		if ($this->translator instanceof ITranslator) {
			$grid->setTranslator($this->translator);
		}

		DataGrid::$icon_prefix = 'fas fa-';

		return $grid;
	}
}
