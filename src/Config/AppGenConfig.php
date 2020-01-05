<?php

declare(strict_types=1);

namespace Archette\AppGen\Config;

use Archette\AppGen\Config\Field\ModelField;

class AppGenConfig
{
	public string $appDir = 'app';
	public ModelField $model;

	public function __construct()
	{
		$this->model = new ModelField();
	}
}
