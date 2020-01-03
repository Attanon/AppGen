<?php

declare(strict_types=1);

namespace Archette\AppGen\Command\Entity;

use Nette\Utils\Strings;

class EntityProperty
{
	private string $name;
	private string $type;
	private string $doctrineType;
	private ?int $doctrineMaxLength;
	private ?string $defaultValue;
	private ?bool $nullable;

	public function __construct(
		string $name,
		string $type,
		string $defaultValue = null,
		?bool $nullable = null,
		?int $doctrineMaxLength = null
	) {
		$this->name = $name;
		$this->nullable = $this->isTypeNullable($type);
		$this->type = $this->formatType($type);
		$this->doctrineType = $this->formatDoctrineType($type);
		$this->doctrineMaxLength = $this->getMaxLength($type);
		$this->defaultValue = $defaultValue;
		$this->nullable = $nullable;
		if ($nullable !== null) {
			$this->nullable = $nullable;
		}
		if ($doctrineMaxLength !== null) {
			$this->doctrineMaxLength = $doctrineMaxLength;
		}
	}

	private function isTypeNullable(string $type): bool
	{
		return Strings::startsWith($type, '?');
	}

	private function getMaxLength(string $type): ?int
	{
		if (count($split = explode(':', $type)) > 1) {
			return (int) $split[1];
		}

		return null;
	}

	private function formatDoctrineType(string $type): string
	{
		$type = trim(strtolower($type), '?');

		if (Strings::contains($type, 'bool')) {
			return 'boolean';
		}

		if (Strings::contains($type, 'int') ) {
			return 'integer';
		}

		return $type;
	}

	private function formatType(string $type): string
	{
		$type = trim(strtolower($type), '?');

		if (Strings::contains($type, 'boolean')) {
			return 'bool';
		}

		if (Strings::contains($type, 'date')) {
			return '\DateTime';
		}

		if (Strings::contains($type, 'integer')) {
			return 'int';
		}

		return $type;
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

	public function getDefaultValue(): ?string
	{
		return $this->defaultValue;
	}

	public function isNullable(): bool
	{
		return $this->nullable;
	}

	public function __toString()
	{
		return $this->getName();
	}
}
