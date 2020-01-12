<?php

declare(strict_types=1);

namespace Archette\AppGen\Generator\Property;

use Nette\Utils\Strings;

class DoctrineEntityProperty implements Property
{
	public const RELATION_MANY_TO_MANY = 'ManyToMany';
	public const RELATION_ONE_TO_MANY = 'OneToMany';
	public const RELATION_MANY_TO_ONE = 'ManyToOne';

	private string $name;
	private string $type;
	private string $doctrineType;
	private ?int $doctrineMaxLength;
	private ?string $defaultValue;
	private ?bool $nullable;
	private ?bool $unique;
	private ?string $relationType = null;

	public function __construct(
		string $name,
		string $typeString,
		string $type,
		string $doctrineType,
		string $defaultValue = null,
		string $relationType = null
	) {
		$this->name = $name;
		$this->nullable = $this->isTypeNullable($typeString);
		$this->unique = $this->isTypeUnique($typeString);
		$this->doctrineMaxLength = $this->getMaxLength($typeString);

		$this->type = $type;
		$this->doctrineType = $doctrineType;

		if ($defaultValue === '""' || $defaultValue === "''") {
			$defaultValue = '';
		}

		if ($defaultValue === '[]') {
			$defaultValue = [];
		}

		if ($defaultValue === 'null') {
			$defaultValue = null;
		}

		if ($defaultValue === 'true' || $defaultValue === 'false') {
			$defaultValue = (bool) $defaultValue;
		}

		$this->defaultValue = $defaultValue;
		$this->relationType = $relationType;
	}

	private function isTypeNullable(string $type): bool
	{
		return Strings::startsWith($type, '?');
	}

	private function isTypeUnique(string $type): bool
	{
		return Strings::contains($type, ' --unique');
	}

	private function getMaxLength(string $type): ?int
	{
		if (count($split = explode('|', $type)) > 1) {
			return (int) $split[1];
		}

		if (count($split = explode(':', $type)) > 1) {
			return (int) $split[1];
		}

		return null;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function getType(): string
	{
		return $this->type;
	}

	public function getDoctrineType(): string
	{
		return $this->doctrineType;
	}

	public function getDoctrineMaxLength(): ?int
	{
		return $this->doctrineMaxLength;
	}

	public function getDefaultValue()
	{
		if ($this->type === 'int' || $this->type === 'float') {
			return (int) $this->defaultValue;
		}

		if ($this->type === 'bool') {
			return $this->defaultValue === 'true' || $this->defaultValue === '1';
		}

		return $this->defaultValue;
	}

	public function isNullable(): bool
	{
		return $this->nullable;
	}

	public function isBoolean(): bool
	{
		return Strings::contains($this->type, 'bool');
	}

	public function isNumeric(): bool
	{
		return Strings::contains($this->type, 'int') || Strings::contains($this->type, 'float');
	}

	public function __toString()
	{
		return $this->getName();
	}

	public function isUnique(): bool
	{
		return $this->unique;
	}

	public function getRelationType(): string
	{
		return $this->relationType;
	}

	public function isRelation(): bool
	{
		return $this->relationType !== null;
	}
}
