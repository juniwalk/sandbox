<?php declare(strict_types=1);

/**
 * @copyright Design Point, s.r.o. (c) 2016
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
use Tracy\ILogger;

final class ErrorPresenter implements IPresenter
{
	use \Nette\SmartObject;

	/** @var ILogger */
	private $logger;


	/**
	 * @param ILogger  $logger
	 */
	public function __construct(ILogger $logger)
	{
		$this->logger = $logger;
	}


	/**
	 * @param  Request  $request
	 * @return IResponse
	 */
	public function run(Request $request): IResponse
	{
		$error = $request->getParameter('exception');

		if ($error instanceof BadRequestException) {
			return new Responses\ForwardResponse($request->setPresenterName('Error4xx'));
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
