<?php

declare(strict_types=1);

namespace Archette\AppGen\Generator\Model;

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

	public function create(string $namespaceString, string $entityName): string
	{
		$file = new PhpFile();

		$file->setStrictTypes();

		$namespace = $file->addNamespace($namespaceString);
		if (Strings::contains($this->config->entity->idType, 'uuid')) {
			$namespace->addUse('Ramsey\Uuid\UuidInterface');
		}

		$class = new ClassType(sprintf('%sFactory', $entityName));
		$create = $class->addMethod('create')->setReturnType($namespaceString . '\\' . $entityName);
		$create->addParameter('data')
			->setType(sprintf('%sData', $namespaceString . '\\' . $entityName));
		$create->addBody(sprintf('return new %s(Uuid::uuid4(), $data);', $entityName));

		$namespace->add($class);

		return preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\r\n\r\n", (string) $file);
	}
}
