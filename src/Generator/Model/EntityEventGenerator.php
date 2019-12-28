<?php

declare(strict_types=1);

namespace Archette\AppGen\Generator\Model;

use Archette\AppGen\Command\Model\CreateModelInput;
use Archette\AppGen\Config\AppGenConfig;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;
use Nette\Utils\Strings;

class EntityEventGenerator
{
	private AppGenConfig $config;

	public function __construct(
		AppGenConfig $config
	) {
		$this->config = $config;
	}

	public function create(CreateModelInput $input, string $eventName): string
	{
		$file = new PhpFile();

		$file->setStrictTypes();

		$namespace = $file->addNamespace($input->getEventNamespace());
		$namespace->addUse($input->getEntityClass(true));

		$class = new ClassType($input->getEventClass($eventName));

		$class->addProperty(Strings::firstLower($input->getEntityClass()))
			->setType($input->getEntityClass(true));

		$constructor = $class->addMethod('__construct');
		$constructor->addParameter(Strings::firstLower($input->getEntityClass()))
			->setType($input->getEntityClass(true));
		$constructor->addBody(sprintf('$this->%1$s = $%1$s;', Strings::firstLower($input->getEntityClass())));

		$get = $class->addMethod(sprintf('get%s', $input->getEntityClass()))
			->setReturnType($input->getEntityClass(true));
		$get->addBody(sprintf('return $this->%s;', Strings::firstLower($input->getEntityClass())));

		$namespace->add($class);

		return (string) $file;
	}
}
