<?php

declare(strict_types=1);

namespace Archette\AppGen\Command;

use Archette\AppGen\Config\AppGenConfig;
use Archette\AppGen\Generator\Model\EntityGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class CreateModelCommand extends Command
{
	private AppGenConfig $config;
	private EntityGenerator $entityGenerator;

	public function __construct(
		AppGenConfig $config,
		EntityGenerator $entityGenerator
	) {
		parent::__construct();
		$this->config = $config;
		$this->entityGenerator = $entityGenerator;
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

		$namespace = $questionHelper->ask($input, $output, new Question('Namespace: '));
		$entityName = $questionHelper->ask($input, $output, new Question('Entity name: '));

		$directory = $this->config->appDir . DIRECTORY_SEPARATOR . str_replace('\\', '/', $namespace) . DIRECTORY_SEPARATOR;

		if (!file_exists($directory)) {
			mkdir($directory, 0777, true);
		}

		file_put_contents($directory . $entityName, $this->entityGenerator->create($namespace, $entityName, []));

		$output->writeln('Done!');
	}
}
