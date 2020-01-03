<?php

declare(strict_types=1);

namespace Archette\AppGen\Command\Entity\Generator;

use Archette\AppGen\Command\Entity\CreateEntityInput;
use Archette\AppGen\Config\AppGenConfig;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;
use Nette\Utils\Strings;

class EntityFactoryGenerator
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

		$namespace = $file->addNamespace($input->getNamespace());
		if (Strings::contains($this->config->entity->idType, 'uuid')) {
			$namespace->addUse('Ramsey\Uuid\UuidInterface');
		}

		$class = new ClassType($input->getFactoryClass());
		$class->setFinal();
		$create = $class->addMethod('create')->setReturnType($input->getFactoryClass(true));
		$create->addParameter('data')
			->setType($input->getFactoryClass(true));
		$create->addBody(sprintf('return new %s(Uuid::uuid4(), $data);', $input->getEntityClass()));

		$namespace->add($class);

		return (string) $file;
	}
}
