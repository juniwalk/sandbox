<?php declare(strict_types=1);

/**
 * @copyright Design Point, s.r.o. (c) 2016
 * @license   MIT License
 */

namespace App\Modules;

use Nette\Application\Request;
use Nette\Application\BadRequestException;

final class Error4xxPresenter extends AbstractPresenter
{
	/**
	 * @return void
	 * @throws BadRequestException
	 */
	public function startup(): void
	{
		parent::startup();

		if (!$this->getRequest()->isMethod(Request::FORWARD)) {
			throw new BadRequestException;
		}
	}


	/**
	 * @param BadRequestException  $exception
	 */
	public function renderDefault(BadRequestException $exception): void
	{
		$file = __DIR__ . '/templates/Error/'.$exception->getCode().'.latte';
		$file = is_file($file) ? $file : __DIR__.'/templates/Error/4xx.latte';

		$template = $this->getTemplate();
		$template->add('code', $exception->getCode());
		$template->setFile($file);
	}
}
