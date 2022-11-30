<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2020
 * @license   MIT License
 */

use App\Bootstrap;
use Nette\Application\Application as HttpApplication;
use Contributte\Console\Application as CliApplication;

require __DIR__.'/../vendor/autoload.php';
$class = php_sapi_name() == 'cli'
	? CliApplication::class
	: HttpApplication::class;

Bootstrap::boot()
	->getByType($class)
	->run();
