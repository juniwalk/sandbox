<?php declare(strict_types=1);

/**
 * @copyright Design Point, s.r.o. (c) 2020
 * @license   MIT License
 */

use Nette\Application\Application as HttpApplication;
use Contributte\Console\Application as CliApplication;

require __DIR__.'/../vendor/autoload.php';
$class = php_sapi_name() == 'cli'
    ? CliApplication::class
    : HttpApplication::class;

App\Bootstrap::boot()->createContainer()
	->getByType($class)->run();
