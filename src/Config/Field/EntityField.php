<?php

declare(strict_types=1);

namespace Archette\AppGen\Config\Field;

class EntityField
{
	public string $idType = 'uuid_binary';
	public ?string $idComment = null;
	public bool $createSetters = false;
	public array $defaultTraits = [
		'removable' => null,
		'timestampable' => null
	];
}
