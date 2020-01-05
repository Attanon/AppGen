<?php

require __DIR__ . '/vendor/autoload.php';

use Archette\AppGen\Command\Model\CreateModelCommand;
use Archette\AppGen\Generator\EntityDataGenerator;
use Archette\AppGen\Generator\EntityEventGenerator;
use Archette\AppGen\Generator\EntityFacadeGenerator;
use Archette\AppGen\Generator\EntityFactoryGenerator;
use Archette\AppGen\Generator\EntityGenerator;
use Archette\AppGen\Generator\EntityNotFoundExceptionGenerator;
use Archette\AppGen\Generator\EntityRepositoryGenerator;
use Archette\AppGen\Config\AppGenConfig;
use Nette\Neon\Neon;
use Nette\Schema\Expect;
use Nette\Schema\Processor;
use Symfony\Component\Console\Application;

if (!file_exists($configFile = 'appgen.neon')) {
	file_put_contents($configFile, Neon::encode(new AppGenConfig(), Neon::BLOCK));
}

$processor = new Processor();

try {
	$config = $processor->process(Expect::from(new AppGenConfig()), Neon::decode(file_get_contents($configFile)));
} catch (Nette\Schema\ValidationException $e) {
	exit(PHP_EOL . 'Config is invalid!: ' . PHP_EOL . $e->getMessage() . PHP_EOL);
}

$application = new Application();

$application->add(new CreateModelCommand(
	$config,
	new EntityGenerator($config),
	new EntityDataGenerator($config),
	new EntityFactoryGenerator($config),
	new EntityRepositoryGenerator($config),
	new EntityFacadeGenerator($config),
	new EntityNotFoundExceptionGenerator($config),
	new EntityEventGenerator($config)
));

$application->setDefaultCommand('appgen:model');

exit($application->run());
