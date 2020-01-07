<?php

declare(strict_types=1);

namespace Archette\AppGen\Generator;

use Archette\AppGen\Command\Model\CreateModelResult;
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

	public function create(CreateModelResult $input): string
	{
		$file = new PhpFile();

		$file->setStrictTypes();

		$namespace = $file->addNamespace($input->getNamespace());
		if (Strings::contains($this->config->model->entity->idType, 'uuid')) {
			$namespace->addUse('Ramsey\Uuid\UuidInterface');
		}

		$class = new ClassType($input->getFactoryClass());
		$class->setFinal();
		$create = $class->addMethod('create')
			->setReturnType($input->getEntityClass(true));
		$create->addParameter('data')
			->setType($input->getDataClass(true));

		if (Strings::contains($this->config->model->entity->idType, 'uuid')) {
			$create->addBody(sprintf('return new %s(Uuid::uuid4(), $data);', $input->getEntityClass()));
		} else {
			$create->addBody(sprintf('return new %s($data);', $input->getEntityClass()));
		}

		$namespace->add($class);

		return (string) $file;
	}
}
