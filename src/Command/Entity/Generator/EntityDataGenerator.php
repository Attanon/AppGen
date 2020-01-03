<?php

declare(strict_types=1);

namespace Archette\AppGen\Command\Entity\Generator;

use Archette\AppGen\Command\Entity\CreateEntityInput;
use Archette\AppGen\Config\AppGenConfig;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;

class EntityDataGenerator
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

		$class = new ClassType($input->getDataClass());

		foreach ($input->getEntityProperties() as $property) {
			$dataProperty = $class->addProperty($property->getName())
				->setType($property->getType())
				->setNullable($property->isNullable())
				->setVisibility(ClassType::VISIBILITY_PUBLIC);

			if ($defaultValue = $property->getDefaultValue()) {
				$dataProperty->setValue($defaultValue);
			}
		}

		$namespace->add($class);

		return (string) $file;
	}
}
