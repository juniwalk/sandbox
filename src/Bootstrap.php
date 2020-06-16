<?php declare(strict_types=1);

/**
 * @copyright Design Point, s.r.o. (c) 2020
 * @license   MIT License
 */

namespace App;

use Nette\Configurator;

final class Bootstrap
{
	/** @var string */
	const IPCONF = __DIR__.'/../config/config-ipconf.php';


	/**
	 * @return Configurator
	 */
	public static function boot(): Configurator
	{
		$configurator = new Configurator;

		if ($ipconf = (@include static::IPCONF)) {
			$configurator->setDebugMode($ipconf);
		}

		$configurator->enableTracy(__DIR__.'/../log');
		$configurator->setTempDirectory(__DIR__.'/../temp');
		$configurator->addConfig(__DIR__.'/../config/config.neon');

		return $configurator;
	}
}
