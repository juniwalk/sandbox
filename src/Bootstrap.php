<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2020
 * @license   MIT License
 */

namespace App;

use Nette\Configurator;

final class Bootstrap
{
	/** @var string */
	const FILE_ACCESS = __DIR__.'/../config/config-access.php';
	const FILE_LOCK = 'lock.phtml';

	/** @var string */
	private static $remoteAddr;

	/** @var bool */
	private static $debugMode;

	/** @var string[] */
	private static $trustedProxies = [
	];


	/**
	 * @return Configurator
	 */
	public static function boot(): Configurator
	{;
		$configurator = new Configurator;
		$configurator->setDebugMode(static::isDebugMode());
		$configurator->enableTracy(__DIR__.'/../log');
		$configurator->setTempDirectory(__DIR__.'/../temp');
		$configurator->addConfig(__DIR__.'/../config/config.neon');

		return $configurator;
	}


	/**
	 * @return bool
	 */
	public static function isLocked(): bool
	{
		return file_exists(getcwd().'/'.static::FILE_LOCK);
	}


	/**
	 * @return string
	 */
	public static function getRemoteAddr(): ?string
	{
		if (is_null(static::$remoteAddr)) {
			static::$remoteAddr = static::detectRemoteAddr();
		}

		return static::$remoteAddr ?: null;
	}


	/**
	 * @return string[]
	 */
	public static function getAllowedList(): iterable
	{
		return array_filter((array) @include static::FILE_ACCESS);
	}


	/**
	 * @return bool
	 */
	public static function isDebugMode(): bool
	{
		if (is_null(static::$debugMode)) {
			static::$debugMode = static::detectDebugMode();
		}

		return static::$debugMode;
	}


	/**
	 * @return bool
	 */
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


	/**
	 * @return string
	 */
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
