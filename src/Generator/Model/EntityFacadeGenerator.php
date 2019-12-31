<?php

declare(strict_types=1);

namespace Archette\AppGen\Generator\Model;

use Archette\AppGen\Command\Model\CreateModelInput;
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

	public function create(CreateModelInput $input): string
	{
		$file = new PhpFile();

		$file->setStrictTypes();

		$namespace = $file->addNamespace($input->getNamespace());
		$namespace->addUse($input->getFactoryClass(true));
		$namespace->addUse('\Doctrine\ORM\EntityManagerInterface');
		$namespace->addUse('\Symfony\Component\EventDispatcher\EventDispatcherInterface');

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
        $constructor->addBody(sprintf('$this->%1$s = $%1$s', $factoryProperty->getName()));
        $constructor->addBody(sprintf('$this->%1$s = $%1$s', $entityManagerProperty->getName()));
        if (isset($eventDispatcherProperty)) {
            $constructor->addBody(sprintf('$this->%1$s = $%1$s', $eventDispatcherProperty->getName()));
        }

        $create = $class->addMethod('create')
            ->setReturnType($input->getEntityClass(true));

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

		$namespace->add($class);

		return (string) $file;
	}
}
