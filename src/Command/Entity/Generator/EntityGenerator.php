<?php

declare(strict_types=1);

namespace Archette\AppGen\Command\Entity\Generator;

use Archette\AppGen\Command\Entity\CreateEntityInput;
use Archette\AppGen\Config\AppGenConfig;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;
use Nette\Utils\Strings;

class EntityGenerator
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

		$namespace->addUse('Doctrine\ORM\Mapping', 'ORM');
		$namespace->addUse('Ramsey\Uuid\UuidInterface');

		$class = new ClassType($input->getEntityClass());

		$class->addComment('@ORM\Entity')
			->addComment('@ORM\HasLifecycleCallbacks')
			->addComment('@ORM\Table(name="' . str_replace('-', '_', Strings::webalize($input->getEntityClass())) . '")');

		$id = $class->addProperty('id');
		$id->setType($this->config->entity->idType === 'uuid' || $this->config->entity->idType === 'uuid_binary' ? 'Ramsey\Uuid\UuidInterface' : 'int')
			->setVisibility('private')
			->addComment('@ORM\Id')
			->addComment('@ORM\Column(type="' . $this->config->entity->idType . '", unique=true)');

		if ($this->config->entity->idType === 'integer') {
			$id->addComment('@ORM\GeneratedValue(strategy="IDENTITY")');
		}

		//TODO: Add default traits

		foreach ($input->getEntityProperties() as $property) {
			$doctrineProperty = $class->addProperty($property->getName())
				->setType($property->getType())
				->setNullable($property->isNullable())
				->setVisibility(ClassType::VISIBILITY_PRIVATE)
				->addComment(sprintf('@ORM\Column(type="%s"%s)', $property->getDoctrineType(), $property->getDoctrineMaxLength() !== null ? ', length="' . $property->getDoctrineMaxLength() . '"' : ''));

			if ($defaultValue = $property->getDefaultValue()) {
				$doctrineProperty->setValue($defaultValue);
			}
		}

		$constructor = $class->addMethod('__construct');

		$constructor->addParameter('id')
			->setType('Ramsey\Uuid\UuidInterface');

		$constructor->addParameter('data')
			->setType($input->getDataClass(true));

		$constructor->addBody('$this->id = $id');

		if ($input->createEditMethod()) {
			$edit = $class->addMethod('edit')
				->setReturnType('void');

			$edit->addParameter('data')
				->setType($input->getDataClass(true));

			foreach ($input->getEntityProperties() as $property) {
				if ($property->getName() !== 'updatedAt' && $property->getName() !== 'createdAt') {
					$edit->addBody(sprintf('$this->%1$s = $data->%1$s', $property->getName()));
				}
			}
		}

		$class->addMethod('getId')
			->setReturnType($this->config->entity->idType === 'uuid' || $this->config->entity->idType === 'uuid_binary' ? 'Ramsey\Uuid\UuidInterface' : 'int')
			->setBody('return $this->id;');

		$namespace->add($class);

		return (string) $file;
	}
}