<?php

declare(strict_types=1);

namespace Archette\AppGen\Command\Model;

use Nette\Utils\Strings;

class CreateModelInput
{
	private string $namespace;
	private string $entityClass;
	private bool $createGetAllMethod;
	private array $getByMethods;
	private array $getAllByMethods;
	private array $events;

	public function __construct(
		string $namespace,
		string $entity,
		bool $createGetAllMethod,
		array $getByMethods = [],
		array $getAllByMethods = [],
		array $events = []
	) {
		$this->namespace = $namespace;
		$this->entityClass = $entity;
		$this->createGetAllMethod = $createGetAllMethod;
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
		return ($withNamespace ? $this->namespace . '\\' . $this->entityClass : $this->entityClass) . 'Repository';
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

	public function getEventClass(string $eventName, bool $withNamespace = false): string
	{
		return ($withNamespace ? $this->getEventNamespace() . '\\' . $this->entityClass : $this->entityClass) . Strings::firstUpper($eventName) . 'Event';
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
}
