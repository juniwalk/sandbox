<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2020
 * @license   MIT License
 */

namespace App\Modules;

use Nette\Application\BadRequestException;
use Nette\Application\Helpers;
use Nette\Application\IPresenter;
use Nette\Application\IResponse;
use Nette\Application\Request;
use Nette\Application\Responses;
use Nette\Http;
use Nette\SmartObject;
use Tracy\ILogger;

final class ErrorPresenter implements IPresenter
{
	use SmartObject;


	public function __construct(
		private ILogger $logger)
	{}


	public function run(Request $request): IResponse
	{
		$error = $request->getParameter('exception');

		if ($error instanceof BadRequestException) {
			[$module, , $sep] = Helpers::splitName($request->getPresenterName());
			$errorPresenter = $module.$sep.'Error4xx';

			return new Responses\ForwardResponse($request->setPresenterName($errorPresenter));
		}

   		$this->logger->log($error, ILogger::EXCEPTION);

		return new Responses\CallbackResponse(
			function(Http\IRequest $request,
			Http\IResponse $response
		): void {
			$contentType = $response->getHeader('Content-Type');

			if (!preg_match('#^text/html(?:;|$)#', $contentType)) {
				return;
			}

			require __DIR__.'/templates/Error/500.phtml';
		});
	}
}
