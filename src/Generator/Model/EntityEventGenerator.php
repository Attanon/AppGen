<?php

declare(strict_types=1);

namespace Archette\AppGen\Generator\Model;

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

	public function create(string $namespaceString, string $entityName, string $eventName): string
	{
		$file = new PhpFile();

		$file->setStrictTypes();

		$namespace = $file->addNamespace($namespaceString . '\\Event');
		$namespace->addUse($namespaceString . '\\' . $entityName);

		$class = new ClassType(sprintf('%s%sEvent', $entityName, Strings::firstUpper($eventName)));

		$class->addProperty(Strings::firstLower($entityName))
			->setType($namespaceString . '\\' . $entityName);

		$constructor = $class->addMethod('__construct');
		$constructor->addParameter(Strings::firstLower($entityName))
			->setType($namespaceString . '\\' . $entityName);
		$constructor->addBody(sprintf('$this->%s = $%s;', $entityName, $entityName));

		$namespace->add($class);

		return preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\r\n\r\n", (string) $file);
	}
}
