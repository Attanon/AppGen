<?php

declare(strict_types=1);

namespace Archette\AppGen\Generator;

use Archette\AppGen\Command\Model\CreateModelResult;
use Archette\AppGen\Config\AppGenConfig;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\Type;

class EntityDataFactoryGenerator
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

		$class = new ClassType($input->getDataFactoryClass());
		$class->setFinal();
		$create = $class->addMethod('createFromFormData')
			->setReturnType($input->getDataClass(true));
		$create->addParameter('formData')
			->setType(Type::ARRAY);
		$create->addBody(sprintf('$data = new %s();', $input->getDataClass()));

		foreach ($input->getEntityProperties() as $property) {
			$create->addBody(sprintf('$data->%1$s = $formData[\'%1$s\'];', $property->getName()));
		}

		$create->addBody('');
		$create->addBody('return $data;');

		$namespace->add($class);

		return (string) $file;
	}
}
