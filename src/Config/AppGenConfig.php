<?php

declare(strict_types=1);

namespace Archette\AppGen\Config;

class AppGenConfig
{
	public string $entityIdType;
	public string $entityIdComment;
	public bool $entityUseDataClass;
	public bool $entityCreateSetters;
}
