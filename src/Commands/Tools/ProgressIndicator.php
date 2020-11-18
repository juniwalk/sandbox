<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2020
 * @license   MIT License
 */

namespace App\Commands\Tools;

use Symfony\Component\Console\Helper;
use Symfony\Component\Console\Output\OutputInterface;

final class ProgressIndicator
{
	/** @var Helper\ProgressBar */
	private $progress;

	/** @var bool */
	private $isHideOnFinish = false;

	/** @var OutputInterface */
	private $output;


	/**
	 * @param OutputInterface  $output
	 * @param int  $redrawFrequency
	 */
	public function __construct(OutputInterface $output, int $redrawFrequency = 100)
	{
		$this->progress = new Helper\ProgressBar($output);
		$this->progress->setRedrawFrequency($redrawFrequency);
		$this->output = $output;
	}


	/**
	 * @param  bool  $hideOnFinish
	 * @return void
	 */
	public function setHideOnFinish(bool $hideOnFinish = true): void
	{
		$this->isHideOnFinish = $hideOnFinish;
	}


	/**
	 * @param  string|null  $message
	 * @param  int|null  $steps
	 * @return void
	 */
	public function start(string $message = null, int $steps = null): void
	{
		$progress = $this->progress;
		$progress->setFormat(" %current% [%bar%] %elapsed%\n %message%");

		if ($steps && $steps > 0) {
			$progress->setFormat(" %current% [%bar%] %percent:3s%%\n %message%");
		}

		if (isset($message)) {
			$progress->setMessage($message);
		}

		$progress->start($steps);
	}


	/**
	 * @param  int  $step
	 * @return void
	 */
	public function advance(int $step = 1): void
	{
		$this->progress->advance($step);
	}


	/**
	 * @param  string|null  $message
	 * @return void
	 */
	public function reset(string $message = null): void
	{
		if (isset($message)) {
			$this->progress->setMessage($message);
		}

		$this->progress->setProgress(0);
		$this->progress->display();
	}


	/**
	 * @param  string|null  $message
	 * @param  bool  $displayFinished
	 * @return void
	 */
	public function message(?string $message, bool $displayFinished = false): void
	{
		if ($displayFinished) {
			$this->progress->finish();
		}

		$this->progress->setMessage($message);
		$this->progress->display();
	}


	/**
	 * @return int
	 */
	public function step(): int
	{
		return $this->progress->getProgress();
	}


	/**
	 * @param  string|null  $message
	 * @return void
	 */
	public function finish(string $message = null): void
	{
		if (isset($message)) {
			$this->progress->setMessage($message);
		}

		$this->progress->finish();

		if ($this->isHideOnFinish) {
			$this->progress->clear();
			return;
		}

		// Make sure there is right amount of padding
		// after progress bar if it is left shown
		$this->output->writeln(PHP_EOL);
	}
}
