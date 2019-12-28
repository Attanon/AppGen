<?php

declare(strict_types=1);

namespace Archette\AppGen\Generator\Model;

use Archette\AppGen\Command\Model\CreateModelInput;
use Archette\AppGen\Config\AppGenConfig;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;

class EntityFacadeGenerator
{
	private AppGenConfig $config;

	public function __construct(
		AppGenConfig $config
	) {
		$this->config = $config;
	}

	public function create(CreateModelInput $input): string
	{
		$file = new PhpFile();

		$file->setStrictTypes();

		$namespace = $file->addNamespace($input->getNamespace());

		$class = new ClassType($input->getFacadeClass());

		$namespace->add($class);

		return (string) $file;
	}
}
