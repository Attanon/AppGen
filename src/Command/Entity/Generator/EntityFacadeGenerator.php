<?php

declare(strict_types=1);

namespace Archette\AppGen\Command\Entity\Generator;

use Archette\AppGen\Command\Entity\CreateEntityInput;
use Archette\AppGen\Config\AppGenConfig;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;
use Nette\Utils\Strings;

class EntityFacadeGenerator
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
		$namespace->addUse($input->getFactoryClass(true));
		$namespace->addUse('\Doctrine\ORM\EntityManagerInterface');
		if ($input->hasEvents()) {
			$namespace->addUse('\Symfony\Component\EventDispatcher\EventDispatcherInterface');
		}
		if (Strings::contains($this->config->entity->idType, 'uuid')) {
			$namespace->addUse('Ramsey\Uuid\UuidInterface');
		}

		$class = new ClassType($input->getFacadeClass());
		$class->setFinal();

		$constructor = $class->addMethod('__construct');

		$factoryProperty = $class->addProperty(Strings::firstLower($input->getFactoryClass()))
			->setType($input->getFactoryClass(true))
			->setVisibility(ClassType::VISIBILITY_PRIVATE);

		$constructor->addParameter($factoryProperty->getName())
			->setType($factoryProperty->getType());

		$entityManagerProperty = $class->addProperty('entityManager')
			->setType('\Doctrine\ORM\EntityManagerInterface')
			->setVisibility(ClassType::VISIBILITY_PRIVATE);

		$constructor->addParameter($entityManagerProperty->getName())
			->setType($entityManagerProperty->getType());

		if ($input->hasEvents()) {
			$eventDispatcherProperty = $class->addProperty('eventDispatcher')
				->setType('\Symfony\Component\EventDispatcher\EventDispatcherInterface')
				->setVisibility(ClassType::VISIBILITY_PRIVATE);

			$constructor->addParameter($eventDispatcherProperty->getName())
				->setType($eventDispatcherProperty->getType());
		}

		$constructor->addBody(sprintf('parent::__construct($%s);', $entityManagerProperty->getName()));
		$constructor->addBody(sprintf('$this->%1$s = $%1$s;', $factoryProperty->getName()));
		$constructor->addBody(sprintf('$this->%1$s = $%1$s;', $entityManagerProperty->getName()));
		if (isset($eventDispatcherProperty)) {
			$constructor->addBody(sprintf('$this->%1$s = $%1$s;', $eventDispatcherProperty->getName()));
		}

		$create = $class->addMethod('create')
			->setReturnType($input->getEntityClass(true))
			->setVisibility(ClassType::VISIBILITY_PUBLIC);

		$create->addParameter('data')
			->setType($input->getDataClass(true));

		$create->addBody(sprintf('$%s = $this->%s->create($data);', Strings::firstLower($input->getEntityClass()), $factoryProperty->getName()));
		$create->addBody('');
		$create->addBody(sprintf('$this->%s->persist($%s);', $entityManagerProperty->getName(), Strings::firstLower($input->getEntityClass())));
		$create->addBody(sprintf('$this->%s->flush();', $entityManagerProperty->getName()));
		$create->addBody('');
		if (isset($eventDispatcherProperty) && $createdEvent = $input->getEventClass('created')) {
			$create->addBody(sprintf('$this->%s->dispatch(new %s($%s));', $eventDispatcherProperty->getName(), $createdEvent, Strings::firstLower($input->getEntityClass())));
			$create->addBody('');
		}
		$create->addBody(sprintf('return $%s;', Strings::firstLower($input->getEntityClass())));

		if ($input->createEditMethod()) {
			$edit = $class->addMethod('edit')
				->setReturnType($input->getEntityClass(true))
				->setVisibility(ClassType::VISIBILITY_PUBLIC);

			$edit->addParameter('id')
				->setType(Strings::contains($this->config->entity->idType, 'uuid') ? 'Ramsey\Uuid\UuidInterface' : 'int');

			$create->addParameter('data')
				->setType($input->getDataClass(true));

			$edit->addBody(sprintf('$%s = $this->get($id);', Strings::firstLower($input->getEntityClass())));
			$edit->addBody('');
			$edit->addBody(sprintf('$%s->edit($data);', Strings::firstLower($input->getEntityClass())));
			$edit->addBody(sprintf('$this->%s->flush();', $entityManagerProperty->getName()));
			$edit->addBody('');
			$edit->addBody(sprintf('return $%s;', Strings::firstLower($input->getEntityClass())));
		}

		if ($input->createSoftDeleteMethod()) {
			//TODO: Implement soft-delete trait
		}

		if ($input->createDeleteMethod()) {
			$delete = $class->addMethod('delete')
				->setReturnType('void')
				->setVisibility(ClassType::VISIBILITY_PUBLIC);

			$delete->addParameter('id')
				->setType(Strings::contains($this->config->entity->idType, 'uuid') ? 'Ramsey\Uuid\UuidInterface' : 'int');

			$delete->addBody(sprintf('$%s = $this->get($id);', Strings::firstLower($input->getEntityClass())));
			$delete->addBody('');
			if (isset($eventDispatcherProperty) && $deletedEvent = $input->getEventClass('deleted')) {
				$delete->addBody(sprintf('$this->%s->dispatch(new %s($%s));', $eventDispatcherProperty->getName(), $deletedEvent, Strings::firstLower($input->getEntityClass())));
				$delete->addBody('');
			}
			$delete->addBody(sprintf('$this->%s->remove($%s);', $entityManagerProperty->getName(), Strings::firstLower($input->getEntityClass())));
			$delete->addBody(sprintf('$this->%s->flush();', $entityManagerProperty->getName()));
		}

		$namespace->add($class);

		return (string) $file;
	}
}
