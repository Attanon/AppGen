<?php

declare(strict_types=1);

namespace Archette\AppGen\Command\Entity;

use Archette\AppGen\Command\Entity\Generator\EntityDataGenerator;
use Archette\AppGen\Command\Entity\Generator\EntityEventGenerator;
use Archette\AppGen\Command\Entity\Generator\EntityFacadeGenerator;
use Archette\AppGen\Command\Entity\Generator\EntityFactoryGenerator;
use Archette\AppGen\Command\Entity\Generator\EntityGenerator;
use Archette\AppGen\Command\Entity\Generator\EntityNotFoundExceptionGenerator;
use Archette\AppGen\Command\Entity\Generator\EntityRepositoryGenerator;
use Archette\AppGen\Config\AppGenConfig;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class CreateEntityCommand extends Command
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
		$this->setName('appgen:entity')
			->setDescription('Create model package with entity');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		/** @var QuestionHelper $questionHelper */
		$questionHelper = $this->getHelper('question');

		$output->getFormatter()->setStyle('blue', new OutputFormatterStyle('blue', 'default', ['bold', 'blink']));
		$output->getFormatter()->setStyle('yellow', new OutputFormatterStyle('yellow', 'default', ['bold', 'blink']));
		$output->getFormatter()->setStyle('question', new OutputFormatterStyle('black', 'blue', ['bold', 'blink']));
		$output->getFormatter()->setStyle('success', new OutputFormatterStyle('white', 'green', ['bold', 'blink']));

		$output->writeln('');
		$output->writeln('<info>#################################################</info>');
		$output->writeln('<info>~</info> Welcome to <blue>AppGen v0.1</blue> created by <blue>Rick Strafy</blue> <info>~</info>');
		$output->writeln('<info>#################################################</info>');
		$output->writeln('');

		$entityName = $questionHelper->ask($input, $output, new Question('# <blue>Entity Name</blue>: '));
		$namespace = trim($questionHelper->ask($input, $output, new Question('# <blue>Namespace</blue>: ')), '\\');
		$output->writeln('');

		/** @var EntityProperty[] $properties */
		$properties = [];

		if ($questionHelper->ask($input, $output, new ConfirmationQuestion('# <blue>Define Entity Properties</blue>? [yes] ', true))) {
			$defineProperty = function () use ($properties, $questionHelper, $input, $output): bool {
				$output->writeln('');
				$name = $questionHelper->ask($input, $output, new Question('# <yellow>Property Name</yellow>: '));
				$type = $questionHelper->ask($input, $output, new Question('# <yellow>Property Type</yellow> (e.g. "<blue>?string|31 --unique</blue>"): '));
				$value = $questionHelper->ask($input, $output, new Question('# <yellow>Default Value</yellow>: '));
				$properties[] = new EntityProperty($name, $type, $value);
				$output->writeln('');
				if ($questionHelper->ask($input, $output, new ConfirmationQuestion('# <blue>Define Another Property</blue>? [yes] ', true))) {
					return true;
				}
				return false;
			};

			while (true) {
				if (!$defineProperty()) {
					break;
				}
			}

			$output->writeln('');
		}

		$input = new CreateEntityInput(
			$namespace,
			$entityName,
			$properties,
			true,
			true,
			true,
			true,
			['test1' => 'string', 'test2' => 'string'],
			['test3' => 'int'],
			['created', 'deleted', 'updated']
		);

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

		$output->writeln('<info>Entity package</info> <blue>successfully</blue> <info>created!</info>');

		return 1;
	}
}
