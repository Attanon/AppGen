<?php

declare(strict_types=1);

namespace Archette\AppGen\Generator\Property\Relation;

class RelationData
{
	public const RELATION_ONE_TO_ONE = 'OneToOne';
	public const RELATION_ONE_TO_MANY = 'OneToMany';
	public const RELATION_MANY_TO_MANY = 'ManyToMany';
	public const RELATION_MANY_TO_ONE = 'ManyToOne';

	private string $type;
	private string $targetClass;
	private string $targetClassName;
	private bool $biDirectional;
	private ?string $cascadeAttribute;
	private bool $onDeleteCascade;

	public function __construct(
		string $relationType,
		string $targetClass,
		string $targetClassName,
		bool $biDirectional,
		string $cascadeAttribute = null,
		bool $onDeleteCascade = false
	) {
		$this->type = $relationType;
		$this->targetClass = $targetClass;
		$this->targetClassName = $targetClassName;
		$this->biDirectional = $biDirectional;
		$this->cascadeAttribute = $cascadeAttribute;
		$this->onDeleteCascade = $onDeleteCascade;
	}

	public function getType(): string
	{
		return $this->type;
	}

	public function isBiDirectional(): bool
	{
		return $this->biDirectional;
	}

	public function getCascadeString(): ?string
	{
		if ($this->cascadeAttribute !== null) {
			return $this->cascadeAttribute === 'all' ? '"persist", "remove"' : ($this->cascadeAttribute === 'persist' ? '"persist"' : '"remove"');
		}

		return null;
	}

	public function getTargetClass(): string
	{
		return $this->targetClass;
	}

	public function getTargetClassName(): string
	{
		return $this->targetClassName;
	}

	public function isOnDeleteCascade(): bool
	{
		return $this->onDeleteCascade;
	}
}
