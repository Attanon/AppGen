<?php

declare(strict_types=1);

namespace Archette\AppGen\DependencyInjection;

use Archette\AppGen\Command\Model\CreateModelCommand;
use Archette\AppGen\Config\AppGenConfig;
use Archette\AppGen\Generator\Model\EntityDataGenerator;
use Archette\AppGen\Generator\Model\EntityEventGenerator;
use Archette\AppGen\Generator\Model\EntityFacadeGenerator;
use Archette\AppGen\Generator\Model\EntityFactoryGenerator;
use Archette\AppGen\Generator\Model\EntityGenerator;
use Archette\AppGen\Generator\Model\EntityNotFoundExceptionGenerator;
use Archette\AppGen\Generator\Model\EntityRepositoryGenerator;
use Nette\DI\CompilerExtension;
use Nette\Schema\Expect;
use Nette\Schema\Schema;

class AppGenExtension extends CompilerExtension
{
	public function __construct()
	{
		$this->config = new AppGenConfig();
	}

	public function getConfigSchema(): Schema
	{
		return Expect::from($this->config);
	}

	public function loadConfiguration(): void
	{
		$this->getContainerBuilder()->addDefinition($this->prefix('createModelCommand'))
			->setFactory(CreateModelCommand::class, [$this->config]);

		$this->getContainerBuilder()->addDefinition($this->prefix('entityGenerator'))
			->setFactory(EntityGenerator::class, [$this->config]);

		$this->getContainerBuilder()->addDefinition($this->prefix('entityDataGenerator'))
			->setFactory(EntityDataGenerator::class, [$this->config]);

		$this->getContainerBuilder()->addDefinition($this->prefix('entityFactoryGenerator'))
			->setFactory(EntityFactoryGenerator::class, [$this->config]);

		$this->getContainerBuilder()->addDefinition($this->prefix('entityRepositoryGenerator'))
			->setFactory(EntityRepositoryGenerator::class, [$this->config]);

		$this->getContainerBuilder()->addDefinition($this->prefix('entityFacadeGenerator'))
			->setFactory(EntityFacadeGenerator::class, [$this->config]);

		$this->getContainerBuilder()->addDefinition($this->prefix('entityNotFoundExceptionGenerator'))
			->setFactory(EntityNotFoundExceptionGenerator::class, [$this->config]);

		$this->getContainerBuilder()->addDefinition($this->prefix('entityEventGenerator'))
			->setFactory(EntityEventGenerator::class, [$this->config]);
	}
}
