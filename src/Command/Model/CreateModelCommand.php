<?php

declare(strict_types=1);

namespace Archette\AppGen\Command\Model;

use Archette\AppGen\Config\AppGenConfig;
use Archette\AppGen\Generator\Model\EntityDataGenerator;
use Archette\AppGen\Generator\Model\EntityEventGenerator;
use Archette\AppGen\Generator\Model\EntityFacadeGenerator;
use Archette\AppGen\Generator\Model\EntityFactoryGenerator;
use Archette\AppGen\Generator\Model\EntityGenerator;
use Archette\AppGen\Generator\Model\EntityNotFoundExceptionGenerator;
use Archette\AppGen\Generator\Model\EntityRepositoryGenerator;
use Generator;
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
	private EntityNotFoundExceptionGenerator $entityNotFoundExceptionGenerator;
	private EntityEventGenerator $entityEventGenerator;

	public function __construct(
		AppGenConfig $config,
		EntityGenerator $entityGenerator,
		EntityDataGenerator $entityDataGenerator,
		EntityFactoryGenerator $entityFactoryGenerator,
		EntityRepositoryGenerator $entityRepositoryGenerator,
		EntityFacadeGenerator $entityFacadeGenerator,
		EntityNotFoundExceptionGenerator $entityNotFoundExceptionGenerator,
		EntityEventGenerator $entityEventGenerator
	) {
		parent::__construct();
		$this->config = $config;
		$this->entityGenerator = $entityGenerator;
		$this->entityDataGenerator = $entityDataGenerator;
		$this->entityFactoryGenerator = $entityFactoryGenerator;
		$this->entityRepositoryGenerator = $entityRepositoryGenerator;
		$this->entityFacadeGenerator = $entityFacadeGenerator;
		$this->entityNotFoundExceptionGenerator = $entityNotFoundExceptionGenerator;
		$this->entityEventGenerator = $entityEventGenerator;
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

		$input = new CreateModelInput($namespace, $entityName, false);

		$filePath = function (string $namespace): string {
			$path = str_replace('\\', '/', $namespace);
			$path = substr($path, strlen(explode('/', $path)[0]));
			$path = $this->config->appDir . $path . '.php';

			if (!file_exists($directory = dirname($path))) {
				mkdir($directory, 0777, true);
			}

			return $path;
		};

		$eventMap = [];
		foreach ($input->getEvents() as $event) {
			$eventMap[$filePath($input->getEventClass($event, true))] = $this->entityEventGenerator->create($input, $event);
		}

		$classMap = array_merge([
			$filePath($input->getEntityClass(true)) => $this->entityGenerator->create($input),
			$filePath($input->getDataClass(true)) => $this->entityDataGenerator->create($input),
			$filePath($input->getFactoryClass(true)) => $this->entityFactoryGenerator->create($input),
			$filePath($input->getRepositoryClass(true)) => $this->entityRepositoryGenerator->create($input),
			$filePath($input->getFacadeClass(true)) => $this->entityFacadeGenerator->create($input),
			$filePath($input->getNotFoundExceptionClass(true)) => $this->entityNotFoundExceptionGenerator->create($input)
		], $eventMap);

		$classMap = array_map(fn($content) => preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\r\n\r\n", $content), $classMap);

		foreach ($classMap as $location => $content) {
			file_put_contents($location, $content);
		}

		$output->writeln('Done');
	}
}
