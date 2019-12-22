<?php

declare(strict_types=1);

namespace Archette\AppGen\Generator\Model;

use Archette\AppGen\Config\AppGenConfig;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;

class EntityGenerator
{
	private AppGenConfig $config;

	public function __construct(
		AppGenConfig $config
	) {
		$this->config = $config;
	}

	public function create(string $namespaceString, string $entityName, array $properties): string
	{
		$file = new PhpFile();

		$file->setStrictTypes();

		$namespace = $file->addNamespace($namespaceString);

		$namespace->addUse('Doctrine\ORM\Mapping', 'ORM');
		$namespace->addUse('Ramsey\Uuid\UuidInterface');

		$class = new ClassType($entityName);

		$class->addComment('@ORM\Entity')
			->addComment('@ORM\HasLifecycleCallbacks')
			->addComment('@ORM\Table(name="' . strtolower($entityName) . '")');

		$id = $class->addProperty('id');
		$id->setType($this->config->entityIdType === 'uuid' || $this->config->entityIdType === 'uuid_binary' ? 'Ramsey\Uuid\UuidInterface' : 'int')
			->setVisibility('private')
			->addComment('@ORM\Id')
			->addComment('@ORM\Column(type="' . $this->config->entityIdType . '", unique=true)');

		if ($this->config->entityIdType === 'integer') {
			$id->addComment('@ORM\GeneratedValue(strategy="IDENTITY")');
		}

		//TODO: Add properties (string, boolean, integer, array,

		/*$class->addProperty('test')
			->setType('int')
			->setNullable()
			->setVisibility('private')
			->addComment('@ORM\Column(type="integer")');*/

		$constructor = $class->addMethod('__construct');

		$constructor->addParameter('id')
			->setType('Ramsey\Uuid\UuidInterface');

		$constructor->addParameter('data')
			->setType($namespace->getName() . '\\' . $entityName . 'Data');

		$class->addMethod('getId')
			->setReturnType($this->config->entityIdType === 'uuid' || $this->config->entityIdType === 'uuid_binary' ? 'Ramsey\Uuid\UuidInterface' : 'int')
			->setBody('return $this->id;');

		$namespace->add($class);

		return preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\r\n\r\n", (string) $file);
	}
}
