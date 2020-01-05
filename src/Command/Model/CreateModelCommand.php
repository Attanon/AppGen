<?php

declare(strict_types=1);

namespace Archette\AppGen\Command\Model;

use Archette\AppGen\Generator\EntityDataGenerator;
use Archette\AppGen\Generator\EntityEventGenerator;
use Archette\AppGen\Generator\EntityFacadeGenerator;
use Archette\AppGen\Generator\EntityFactoryGenerator;
use Archette\AppGen\Generator\EntityGenerator;
use Archette\AppGen\Generator\EntityNotFoundExceptionGenerator;
use Archette\AppGen\Generator\EntityRepositoryGenerator;
use Archette\AppGen\Config\AppGenConfig;
use Archette\AppGen\Generator\Property\DoctrineEntityProperty;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
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

		/** @var DoctrineEntityProperty[] $properties */
		$properties = [];
		$propertyNames = [];

		if ($questionHelper->ask($input, $output, new ConfirmationQuestion('# <blue>Define Entity Properties</blue>? [<info>yes</info>] ', true))) {
			$lazyName = null;
			while (true) {
				$output->writeln('');
				if ($lazyName !== null) {
					$name = $lazyName;
					$output->writeln(sprintf('# <yellow>Property Name</yellow>: %s', $name));
				} else {
					$name = $questionHelper->ask($input, $output, new Question('# <yellow>Property Name</yellow>: '));
				}
				$type = $questionHelper->ask($input, $output, new Question('# <yellow>Type</yellow> (e.g. "<blue>?string|31 --unique</blue>") [<info>string</info>]: ', 'string'));
				$value = $questionHelper->ask($input, $output, new Question('# <yellow>Default Value</yellow>: '));
				$output->writeln('');

				$properties[] = new DoctrineEntityProperty((string) $name, $type, $value);
				$propertyNames[] = $name;

				$defineAnother = $questionHelper->ask($input, $output, new Question('# <blue>Define Another Property</blue>? [<info>yes</info>] '));
				if ($defineAnother === null || strtolower($defineAnother) === 'yes' || strtolower($defineAnother) === 'y') {
					$lazyName = null;
					continue;
				} else if (strtolower($defineAnother) !== 'no' && strtolower($defineAnother) !== 'n') {
					$lazyName = $defineAnother;
					continue;
				}

				break;
			}
		}

		$output->writeln('');
		$createEditMethod = $questionHelper->ask($input, $output, new ConfirmationQuestion('# <blue>Create <yellow>edit</yellow> Method</blue>? [<info>yes</info>] ', true));
		$createGetAllMethod = $questionHelper->ask($input, $output, new ConfirmationQuestion('# <blue>Create <yellow>getAll</yellow> Method</blue>? [<info>yes</info>] ', true));
		$createDeleteMethod = $questionHelper->ask($input, $output, new ConfirmationQuestion('# <blue>Create <yellow>delete</yellow> Method</blue>? [<info>yes</info>] ', true));
		$output->writeln('');

		$getByMethods = [];
		while (true) {
			$getByMethods = $questionHelper->ask($input, $output, new Question('# <blue>Define Fields for <yellow>getBy<Field></yellow> Methods (e.g. "<yellow>email, slug</yellow>")</blue>: ', []));
			if (is_string($getByMethods)) {
				$getByMethods = explode(',', str_replace(' ', '', $getByMethods));
			}

			foreach ($getByMethods as $getByMethod) {
				if (!in_array($getByMethod, $propertyNames)) {
					$output->writeln('');
					$output->writeln(sprintf('<error>Error! Property "%s" does not exist!</error>', $getByMethod));
					$output->writeln('');
					continue 2;
				}
			}

			break;
		}

		$getAllByMethods = [];
		while (true) {
			$getAllByMethods = $questionHelper->ask($input, $output, new Question('# <blue>Define Fields for <yellow>getAllBy<Field></yellow> Methods (e.g. "<yellow>author, type</yellow>")</blue>: ', []));
			if (is_string($getAllByMethods)) {
				$getAllByMethods = explode(',', str_replace(' ', '', $getAllByMethods));
			}

			foreach ($getAllByMethods as $getAllByMethod) {
				if (!in_array($getAllByMethod, $propertyNames)) {
					$output->writeln('');
					$output->writeln(sprintf('<error>Error! Property "%s" does not exist!</error>', $getAllByMethod));
					$output->writeln('');
					continue 2;
				}
			}

			break;
		}

		$events = $questionHelper->ask($input, $output, new Question('# <blue>Define event names (for "<yellow>created, updated, deleted</yellow>" type "<yellow>all</yellow>")</blue>: ', []));
		if (is_string($events)) {
			if ($events === 'all') {
				$events = 'created, updated, deleted';
			}
			$events = explode(',', str_replace(' ', '', $events));
		}

		$output->writeln('');

		$input = new CreateModelResult(
			$namespace,
			$entityName,
			$properties,
			$createGetAllMethod,
			$createEditMethod,
			$createDeleteMethod,
			false,
			$getByMethods,
			$getAllByMethods,
			$events
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

		$output->writeln('<yellow>Entity</yellow>, <yellow>DTO</yellow>, <yellow>Factory</yellow>, <yellow>Repository</yellow>, <yellow>Facade</yellow> and <yellow>Events</yellow> were <info>successfully</info> created!');
		$output->writeln('');

		$output->writeln('Files created:');
		foreach ($classMap as $file => $class) {
			$output->writeln(sprintf('<info>%s</info>', $file));
		}

		return 1;
	}
}
