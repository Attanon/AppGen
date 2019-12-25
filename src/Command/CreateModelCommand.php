<?php

declare(strict_types=1);

namespace Archette\AppGen\Command;

use Archette\AppGen\Config\AppGenConfig;
use Archette\AppGen\Generator\Model\EntityDataGenerator;
use Archette\AppGen\Generator\Model\EntityFacadeGenerator;
use Archette\AppGen\Generator\Model\EntityFactoryGenerator;
use Archette\AppGen\Generator\Model\EntityGenerator;
use Archette\AppGen\Generator\Model\EntityRepositoryGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class CreateModelCommand extends Command
{
	private AppGenConfig $config;
	private EntityGenerator $entityGenerator;
	private EntityDataGenerator $entityDataGenerator;
	private EntityFactoryGenerator $entityFactoryGenerator;
	private EntityRepositoryGenerator $entityRepositoryGenerator;
	private EntityFacadeGenerator $entityFacadeGenerator;

	public function __construct(
		AppGenConfig $config,
		EntityGenerator $entityGenerator,
		EntityDataGenerator $entityDataGenerator,
		EntityFactoryGenerator $entityFactoryGenerator,
		EntityRepositoryGenerator $entityRepositoryGenerator,
		EntityFacadeGenerator $entityFacadeGenerator
	) {
		parent::__construct();
		$this->config = $config;
		$this->entityGenerator = $entityGenerator;
		$this->entityDataGenerator = $entityDataGenerator;
		$this->entityFactoryGenerator = $entityFactoryGenerator;
		$this->entityRepositoryGenerator = $entityRepositoryGenerator;
		$this->entityFacadeGenerator = $entityFacadeGenerator;
	}

	protected function configure(): void
	{
		$this->setName('appgen:model')
			->setDescription('Create model package with entity');
	}

	protected function execute(InputInterface $input, OutputInterface $output): void
	{
		/** @var QuestionHelper $questionHelper */
		$questionHelper = $this->getHelper('question');

		$namespace = trim($questionHelper->ask($input, $output, new Question('Namespace: ')), '\\');
		$entityName = $questionHelper->ask($input, $output, new Question('Entity name: '));


		$directory = str_replace('\\', '/', $namespace);
		$directory = ltrim($directory, explode('/', $directory)[0], );
		$directory = substr($directory, strlen(explode('/', $directory)[0]));
		$directory = $this->config->appDir . $directory . DIRECTORY_SEPARATOR;

		if (!file_exists($directory)) {
			mkdir($directory, 0777, true);
		}

		file_put_contents($directory . $entityName . '.php', $this->entityGenerator->create($namespace, $entityName, []));
		file_put_contents($directory . $entityName . 'Data.php', $this->entityDataGenerator->create($namespace, $entityName, []));
		file_put_contents($directory . $entityName . 'Factory.php', $this->entityFactoryGenerator->create($namespace, $entityName));
		file_put_contents($directory . $entityName . 'Repository.php', $this->entityGenerator->create($namespace, $entityName));
		file_put_contents($directory . $entityName . 'Facade.php', $this->entityGenerator->create($namespace, $entityName));

		$output->writeln('Done!');
	}
}
