<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2020
 * @license   MIT License
 */

namespace App\Modules;

use Nette\Application\Request;
use Nette\Application\BadRequestException;

final class Error4xxPresenter extends AbstractPresenter
{
	/**
	 * @throws BadRequestException
	 */
	public function startup(): void
	{
		parent::startup();

		if (!$this->getRequest()->isMethod(Request::FORWARD)) {
			throw new BadRequestException;
		}
	}


	public function renderDefault(BadRequestException $exception): void
	{
		$file = __DIR__.'/templates/Error/'.$exception->getCode().'.latte';
		$code = $page = $exception->getCode();

		if (!is_file($file)) {
			$file = __DIR__.'/templates/Error/4xx.latte';
			$page = '4xx';
		}

		$template = $this->getTemplate();
		$template->pageName = 'error-'.$page;
		$template->code = $code;
		$template->setFile($file);
	}
}
