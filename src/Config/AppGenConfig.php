<?php

declare(strict_types=1);

namespace Archette\AppGen\Config;

use Archette\AppGen\Config\Field\EntityField;

class AppGenConfig
{
	public string $appDir = 'app';
	public EntityField $entity;

	public function __construct(?array $config)
	{
		if ($config !== null) {
			$this->appDir = $config['appDir'];
			$this->entity = new EntityField($config['entity']);
		} else {
			$this->entity = new EntityField([]);
		}
	}
}
