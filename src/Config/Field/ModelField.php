<?php

declare(strict_types=1);

namespace Archette\AppGen\Config\Field;

class ModelField
{
	public EntityField $entity;
	public bool $symfonyEvents = true;

	public function __construct()
	{
		$this->entity = new EntityField();
	}
}
