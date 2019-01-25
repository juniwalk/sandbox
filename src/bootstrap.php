<?php declare(strict_types=1);

/**
 * @copyright Design Point, s.r.o. (c) 2016
 * @license   MIT License
 */

include __DIR__.'/../vendor/autoload.php';

$configurator = new Nette\Configurator;
$configurator->setDebugMode(@include __DIR__.'/../config/config-ipconf.php');
$configurator->setTempDirectory(__DIR__.'/../temp');
$configurator->enableTracy(__DIR__.'/../log');
$configurator->addConfig(__DIR__.'/../config/config.neon');

return $configurator->createContainer();
