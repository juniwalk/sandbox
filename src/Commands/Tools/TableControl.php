<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2020
 * @license   MIT License
 */

namespace App\Commands\Tools;

use Exception;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Output\OutputInterface;

final class TableControl
{
	/** @var OutputInterface */
	private $output;

	/** @var Table */
	private $table;

	/** @var int */
	private $columns = 0;

	/** @var mixed[] */
	private $rows = [];


	/**
	 * @param OutputInterface  $output
	 */
	public function __construct(OutputInterface $output)
	{
		$this->output = $output;
		$this->table = new Table($output);
	}


	/**
	 * @param  string  $columns ...
	 * @return void
	 */
	public function setHeaders(string ... $columns): void
	{
		$this->table->setHeaders($columns);
		$this->columns = sizeof($columns);
	}


	/**
	 * @param  int  $id
	 * @param  mixed  $columns ...
	 * @return void
	 */
	public function setRow(int $id, ... $columns): void
	{
		$this->rows[$id] = $columns;
	}


	/**
	 * @return void
	 */
	public function addSeparator(): void
	{
		$this->rows[] = new TableSeparator;
	}


	/**
	 * @param  string  $message
	 * @param  mixed  $columns ...
	 * @return void
	 */
	public function setSummary(string $message, ... $columns): void
	{
		$span = $this->columns - sizeof($columns);

		$this->rows[] = new TableSeparator;
		$this->rows[] = array_merge([
			new TableCell($message, ['colspan' => $span]),
		], $columns);
	}

	
	/**
	 * @return void
	 */
	public function render(): void
	{
		$this->table->setRows($this->rows);
		$this->table->render();
	}
}
