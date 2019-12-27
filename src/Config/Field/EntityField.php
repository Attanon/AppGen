<?php

declare(strict_types=1);

namespace Archette\AppGen\Config\Field;

class EntityField
{
	public string $idType = 'uuid_binary';
	public bool $symfonyEvents = true;
	public ?string $idComment = null;
	public bool $useDataClass = true;
	public bool $createSetters = false;
	public array $defaultTraits = [];
	public ?string $removableTrait = null;
	public ?string $timestampableTrait = null;
}
