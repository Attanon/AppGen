<?php

declare(strict_types=1);

namespace Archette\AppGen\Generator;

use Archette\AppGen\Command\Model\CreateModelResult;
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

	public function create(CreateModelResult $input): string
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

			if ($property->getDefaultValue() !== null || $property->isNullable()) {
				$dataProperty->setValue($property->getDefaultValue());
			}
		}

		$namespace->add($class);

		return str_replace("\n\n\t", "\n\t", (string) $file);
		//return str_replace(["\r", "\n", "\t"], ['\r', '\n', '\t'], (string) $file);
	}
}
