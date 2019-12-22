<?php

declare(strict_types=1);

namespace Archette\AppGen\Command;

use Archette\AppGen\Generator\Model\EntityGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class CreateModelCommand extends Command
{
	private EntityGenerator $entityGenerator;
	protected static string $defaultName = 'appgen:model';

	public function __construct(
		EntityGenerator $entityGenerator
	) {
		parent::__construct();
		$this->entityGenerator = $entityGenerator;
	}

	protected function configure(): void
	{
		$this->setName(self::$defaultName)
			->setDescription('Create model package with entity');
	}

	protected function execute(InputInterface $input, OutputInterface $output): void
	{
		/** @var QuestionHelper $questionHelper */
		$questionHelper = $this->getHelper('question');

		$namespace = $questionHelper->ask($input, $output, new Question('Namespace: '));
		$entityName = $questionHelper->ask($input, $output, new Question('Entity name: '));

		$output->writeln('Done!');
	}
}
