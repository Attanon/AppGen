<?php

declare(strict_types=1);

namespace Archette\AppGen\DependencyInjection;

use Archette\AppGen\Config\AppGenConfig;
use Nette\DI\CompilerExtension;
use Nette\Schema\Expect;
use Nette\Schema\Schema;

class AppGenExtension extends CompilerExtension
{
	public function __construct()
	{
		$this->config = new AppGenConfig();
	}

	public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'entity' => Expect::structure([
				'idType' => Expect::string(),
				'idComment' => Expect::string()->nullable(),
				'useDataClass' => Expect::bool()->default(true),
				'createSetters' => Expect::bool()->default(false),
			])
		]);
	}

	public function loadConfiguration(): void
	{
		//TODO: Register classes to DI
	}
}
