<?php

declare(strict_types=1);

namespace Archette\AppGen\Config;

use Archette\AppGen\Config\Field\EntityField;

class AppGenConfig
{
	public string $appDir;
	public EntityField $entity;

	public function __construct(string $appDir = '%appDir%')
	{
		$this->appDir = $appDir;
		$this->entity = new EntityField();
	}

	public function loadConfig(AppGenConfig $appGenConfig): void
	{
		$this->appDir = $appGenConfig->appDir;
		$this->entity = $appGenConfig->entity;
	}
}
