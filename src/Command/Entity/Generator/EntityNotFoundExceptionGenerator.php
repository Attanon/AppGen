<?php

declare(strict_types=1);

namespace Archette\AppGen\Command\Entity\Generator;

use Archette\AppGen\Command\Entity\CreateEntityInput;
use Archette\AppGen\Config\AppGenConfig;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;

class EntityNotFoundExceptionGenerator
{
	private AppGenConfig $config;

	public function __construct(
		AppGenConfig $config
	) {
		$this->config = $config;
	}

	public function create(CreateEntityInput $input): string
	{
		$file = new PhpFile();

		$file->setStrictTypes();

		$namespace = $file->addNamespace($input->getExceptionNamespace());
		$namespace->addUse('Exception');

		$class = new ClassType($input->getNotFoundExceptionClass());
		$class->setFinal();
		$class->addExtend('Exception');

		$namespace->add($class);

		return (string) $file;
	}
}