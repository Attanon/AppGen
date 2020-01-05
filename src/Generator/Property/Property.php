<?php

declare(strict_types=1);

namespace Archette\AppGen\Generator\Property;

interface Property
{
	public function getName(): string;
	public function getType(): string;
	public function getDefaultValue();
	public function isNullable(): bool;
}
