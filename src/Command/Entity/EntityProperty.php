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
	private ?bool $unique;

	public function __construct(
		string $name,
		string $type,
		string $defaultValue = null
	) {
		$this->name = $name;
		$this->nullable = $this->isTypeNullable($type);
		$this->unique = $this->isTypeUnique($type);
		$this->doctrineMaxLength = $this->getMaxLength($type);

		$type = explode(':', explode('|', trim(explode(' ', $type)[0], '?'))[0])[0];

		$this->type = $this->formatType($type);
		$this->doctrineType = $this->formatDoctrineType($type);

		if ($defaultValue === '""') {
			$defaultValue = '';
		}

		if ($defaultValue === 'null') {
			$defaultValue = null;
		}

		if ($defaultValue === 'true') {
			$defaultValue = true;
		}

		if ($defaultValue === 'false') {
			$defaultValue = false;
		}

		$this->defaultValue = $defaultValue;
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

		if (Strings::contains($type, 'text')) {
			return 'string';
		}

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
}
