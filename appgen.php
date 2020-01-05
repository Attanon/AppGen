<?php

declare(strict_types=1);

require 'vendor/autoload.php';

use Archette\AppGen\Command\Entity\CreateEntityCommand;
use Archette\AppGen\Command\Entity\Generator\EntityDataGenerator;
use Archette\AppGen\Command\Entity\Generator\EntityEventGenerator;
use Archette\AppGen\Command\Entity\Generator\EntityFacadeGenerator;
use Archette\AppGen\Command\Entity\Generator\EntityFactoryGenerator;
use Archette\AppGen\Command\Entity\Generator\EntityGenerator;
use Archette\AppGen\Command\Entity\Generator\EntityNotFoundExceptionGenerator;
use Archette\AppGen\Command\Entity\Generator\EntityRepositoryGenerator;
use Archette\AppGen\Config\AppGenConfig;
use Nette\Neon\Neon;
use Symfony\Component\Console\Application;

if (!file_exists($configFile = 'appgen.neon')) {
	file_put_contents($configFile, Neon::encode(new AppGenConfig(null), Neon::BLOCK));
}

$application = new Application();
$config = new AppGenConfig(Neon::decode(file_get_contents($configFile)));

$application->add(new CreateEntityCommand(
	$config,
	new EntityGenerator($config),
	new EntityDataGenerator($config),
	new EntityFactoryGenerator($config),
	new EntityRepositoryGenerator($config),
	new EntityFacadeGenerator($config),
	new EntityNotFoundExceptionGenerator($config),
	new EntityEventGenerator($config)
));

$application->setDefaultCommand('appgen:entity');

exit($application->run());
