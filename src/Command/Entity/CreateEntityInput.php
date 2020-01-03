<?php

declare(strict_types=1);

namespace Archette\AppGen\Command\Entity;

use Nette\Utils\Strings;

class CreateEntityInput
{
	private string $namespace;
	private string $entityClass;
	private bool $createGetAllMethod;
	private bool $createEditMethod;
	private bool $createDeleteMethod;
	private bool $createSoftDeleteMethod;
	private array $getByMethods;
	private array $getAllByMethods;
	private array $events;

	/** @var EntityProperty[] */
	private array $entityProperties;

	public function __construct(
		string $namespace,
		string $entity,
		array $entityProperties,
		bool $createGetAllMethod,
		bool $createEditMethod,
		bool $createDeleteMethod,
		bool $createSoftDeleteMethod,
		array $getByMethods = [],
		array $getAllByMethods = [],
		array $events = []
	) {
		$this->namespace = $namespace;
		$this->entityClass = $entity;
		$this->entityProperties = $entityProperties;
		$this->createGetAllMethod = $createGetAllMethod;
		$this->createEditMethod = $createEditMethod;
		$this->createDeleteMethod = $createDeleteMethod;
		$this->createSoftDeleteMethod = $createSoftDeleteMethod;
		$this->getByMethods = $getByMethods;
		$this->getAllByMethods = $getAllByMethods;
		$this->events = $events;
	}

	public function getNamespace(): string
	{
		return $this->namespace;
	}

	public function getEventNamespace(): string
	{
		return $this->namespace . '\\Event';
	}

	public function getExceptionNamespace(): string
	{
		return $this->namespace . '\\Exception';
	}

	public function getEntityClass(bool $withNamespace = false): string
	{
		return $withNamespace ? $this->namespace . '\\' . $this->entityClass : $this->entityClass;
	}

	public function getDataClass(bool $withNamespace = false): string
	{
		return ($withNamespace ? $this->namespace . '\\' . $this->entityClass : $this->entityClass) . 'Data';
	}

	public function getFactoryClass(bool $withNamespace = false): string
	{
		return ($withNamespace ? $this->namespace . '\\' . $this->entityClass : $this->entityClass) . 'Factory';
	}

	public function getRepositoryClass(bool $withNamespace = false): string
	{
		return ($withNamespace ? $this->namespace . '\\' . $this->entityClass : $this->entityClass) . 'Repository';
	}

	public function getFacadeClass(bool $withNamespace = false): string
	{
		return ($withNamespace ? $this->namespace . '\\' . $this->entityClass : $this->entityClass) . 'Facade';
	}

	public function getNotFoundExceptionClass(bool $withNamespace = false): string
	{
		return ($withNamespace ? $this->getExceptionNamespace() . '\\' . $this->entityClass : $this->entityClass) . 'NotFoundException';
	}

	public function getEventClass(string $eventName, bool $withNamespace = false): ?string
	{
		return in_array($eventName, $this->events) ? (($withNamespace ? $this->getEventNamespace() . '\\' . $this->entityClass : $this->entityClass) . Strings::firstUpper($eventName) . 'Event') : null;
	}

	public function isCreateGetAllMethod(): bool
	{
		return $this->createGetAllMethod;
	}

	public function getGetByMethods(): array
	{
		return $this->getByMethods;
	}

	public function getGetAllByMethods(): array
	{
		return $this->getAllByMethods;
	}

	public function getEvents(): array
	{
		return $this->events;
	}

	public function hasEvents(): bool
	{
		return !empty($this->events);
	}

	public function createDeleteMethod(): bool
	{
		return $this->createDeleteMethod;
	}

	public function createEditMethod(): bool
	{
		return $this->createEditMethod;
	}

	public function createSoftDeleteMethod(): bool
	{
		return $this->createSoftDeleteMethod;
	}

	/** @return EntityProperty[] */
	public function getEntityProperties(): array
	{
		return $this->entityProperties;
	}
}
