<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace App;

use JuniWalk\Utils\Html;
use Nette\Bootstrap\Configurator;
use Nette\DI\Container;
use Nette\Localization\Translator;

final class Bootstrap
{
	/** @var string */
	const FILE_ACCESS = __DIR__.'/../config/config-access.php';
	const FILE_LOCK = 'lock.phtml';

	private static ?bool $debugMode = null;
	private static ?string $remoteAddr = null;
	private static array $trustedProxies = [];


	public static function boot(): Container
	{;
		$configurator = new Configurator;
		$configurator->setDebugMode(static::isDebugMode());
		$configurator->enableTracy(__DIR__.'/../log');
		$configurator->setTempDirectory(__DIR__.'/../temp');
		$configurator->addConfig(__DIR__.'/../config/config.neon');
		$configurator->addStaticParameters([
			'appDir' => __DIR__.'/../src',
			'wwwDir' => __DIR__.'/../www',
			'vendorDir' => __DIR__.'/../vendor',
		]);

		$container = $configurator->createContainer();

		if ($translator = $container->getByType(Translator::class)) {
			Html::setTranslator($translator);
		}

		return $container;
	}


	public static function isLocked(): bool
	{
		return file_exists(getcwd().'/'.static::FILE_LOCK);
	}


	public static function getRemoteAddr(): ?string
	{
		if (is_null(static::$remoteAddr)) {
			static::$remoteAddr = static::detectRemoteAddr();
		}

		return static::$remoteAddr ?: null;
	}


	public static function getAllowedList(): array
	{
		return array_filter((array) @include static::FILE_ACCESS);
	}


	public static function isDebugMode(): bool
	{
		if (is_null(static::$debugMode)) {
			static::$debugMode = static::detectDebugMode();
		}

		return static::$debugMode;
	}


	private static function detectDebugMode(): bool
	{
		if (php_sapi_name() == 'cli') {
			return true;
		}

		$allowedList = static::getAllowedList();
		$remoteAddr = static::getRemoteAddr();

		if (!in_array($remoteAddr, $allowedList)) {
			@include getcwd().'/'.static::FILE_LOCK;
		}

		return in_array($remoteAddr, $allowedList);
	}


	private static function detectRemoteAddr(): string
	{
		$remoteAddr = $_SERVER['REMOTE_ADDR'] ?? '';
		$isTrustedProxy = in_array($remoteAddr, Bootstrap::$trustedProxies);

		if ($remoteAddr && $isTrustedProxy && isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$client_ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
			return array_shift($client_ips);
		}

		if ($remoteAddr && $isTrustedProxy && isset($_SERVER['HTTP_CLIENT_IP'])) {
			$client_ips = explode(',', $_SERVER['HTTP_CLIENT_IP']);
			return array_shift($client_ips);
		}

		return $remoteAddr;
	}
}
