<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2020
 * @license   MIT License
 */

namespace App\Commands\Tools;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

final class StatusIndicator
{
	/** @var string */
	const WORKING = '....';
	const SUCCESS = '<info> ok </info>';
	const WARNING = '<comment>warn</comment>';
	const ERROR = '<fail>FAIL</fail>';
	const SKIPPED = '<comment>skip</comment>';

	/** @param OutputInterface */
	private $output;

	/** @param ProgressBar */
	private $progress;

	/** @var bool */
	private $throwExceptions = false;


	/**
	 * @param OutputInterface  $output
	 */
	public function __construct(OutputInterface $output)
	{
		$this->progress = new ProgressBar($output);
		$this->progress->setFormat('[%status%] %message%');
		$this->output = $output;
	}


	/**
	 * @param  bool  $throwExceptions
	 * @return void
	 */
	public function setThrowExceptions(bool $throwExceptions = false): void
	{
		$this->throwExceptions = $throwExceptions;
	}


	/**
	 * @param  string  $message
	 * @return void
	 */
	public function setMessage(string $message): void
	{
		$this->progress->setMessage($message);
	}


	/**
	 * @param  string  $status
	 * @return void
	 */
	public function setStatus(string $status): void
	{
		$this->progress->setMessage($status, 'status');
		$this->progress->advance();
	}


	/**
	 * @param  callable  $callback
	 * @return int
	 */
	public function execute(callable $callback): int
	{
		$progress = $this->progress;
		$progress->setMessage($this::WORKING, 'status');
		$progress->start();

		$renderer = new ExceptionRenderer($this->output);
		$renderer->setBeforeRender([$progress, 'clear']);
		$renderer->setAfterRender([$progress, 'display']);

		try {
			$code = $callback($this) ?: 0;

			if ($progress->getMessage('status') === $this::WORKING) {
				$this->setStatus($this::SUCCESS);
			}

		} catch (Throwable $e) {
			$code = $e->getCode() ?: 1;

			if ($this->throwExceptions) {
				throw $e;
			}

			$this->setStatus($this::ERROR);
			$renderer->render($e);
		}

		$progress->finish();

		// Make sure there is right amount of padding
		// after progress bar if it is left shown
		$this->output->writeln('');

		return $code;
	}
}
