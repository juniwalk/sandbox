<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2020
 * @license   MIT License
 */

namespace App\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

abstract class AbstractCommand extends Command
{
	/** @var InputInterface */
	private $input;

	/** @var OutputInterface */
	private $output;

	/** @var string|null */
	private $confirm;


	/**
	 * @param  string  $message
	 * @return void
	 */
	public function setConfirm(string $message): void
	{
		$this->confirm = $message;
	}


	/**
	 * @param  InputInterface  $input
	 * @param  OutputInterface  $output
	 */
	protected function initialize(InputInterface $input, OutputInterface $output)
	{
		$this->input = $input;
		$this->output = $output;

		$formatter = $output->getFormatter();
		$formatter->setStyle('blue', new OutputFormatterStyle('blue'));
		$formatter->setStyle('fail', new OutputFormatterStyle('red'));
	}


	/**
	 * @param  InputInterface   $input
	 * @param  OutputInterface  $output
	 * @return void
	 */
	protected function interact(InputInterface $input, OutputInterface $output): void
	{
		if (!$this->confirm) {
			return;
		}

		$question = new ConfirmationQuestion($this->confirm);

		if ($this->ask($input, $output, $question)) {
			$output->writeln('');
			return;
		}

		$this->terminate();
	}


	/**
	 * @return void
	 */
	protected function terminate(): void
	{
		$this->setCode(function(): int {
			return 0;
		});
	}


	/**
	 * @param  Question  $question
	 * @return mixed
	 */
	protected function ask(Question $question)
	{
		return $this->getHelper('question')->ask(
			$this->input,
			$this->output,
			$question
		);
	}
}
