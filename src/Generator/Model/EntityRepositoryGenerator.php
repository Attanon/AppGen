<?php

declare(strict_types=1);

namespace Archette\AppGen\Generator\Model;

use Archette\AppGen\Config\AppGenConfig;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;
use Nette\Utils\Strings;

class EntityRepositoryGenerator
{
	private AppGenConfig $config;

	public function __construct(
		AppGenConfig $config
	) {
		$this->config = $config;
	}

	public function create(string $namespaceString, string $entityName, bool $createGetAllMethod, array $getByMethods = [], array $getAllByMethods = []): string
	{
		$file = new PhpFile();

		$file->setStrictTypes();

		$namespace = $file->addNamespace($namespaceString);
		if (in_array('DateTime', array_merge(array_values($getByMethods), array_values($getAllByMethods)))) {
			$namespace->addUse('DateTime');
		}
		$namespace->addUse('Doctrine\ORM\EntityManagerInterface');
		$namespace->addUse('Doctrine\ORM\EntityRepository');
		$namespace->addUse('Doctrine\ORM\QueryBuilder');
		$namespace->addUse('Ramsey\Uuid\UuidInterface');
		$namespace->addUse($namespaceString . '\Exception\\' . $entityName . 'NotFoundException');

		$class = new ClassType($entityName . 'Repository');
		$class->setAbstract();

		$class->addProperty('entityManager')
			->setVisibility('private')
			->setType('Doctrine\ORM\EntityManagerInterface');

		$constructor = $class->addMethod('__construct');
		$constructor->addParameter('entityManager')
			->setType('Doctrine\ORM\EntityManagerInterface');
		$constructor->addBody('$this->entityManager = $entityManager;');

		$class->addMethod('getRepository')
			->setVisibility('private')
			->addComment('@return EntityRepository|ObjectRepository')
			->addBody('return $this->entityManager->getRepository(' . $entityName . '::class);');

		$get = $class->addMethod('get');
		$get->addParameter('id')
			->setType('Ramsey\Uuid\UuidInterface');
		$get->setReturnType($namespaceString . '\\' . $entityName);
		$get->setVisibility('public')
			->addComment('@throws ' . $entityName . 'NotFoundException');

		foreach ($this->createGetByBody($entityName, 'id') as $code) {
			$get->addBody($code);
		}

		foreach ($getByMethods as $fieldName => $fieldType) {
			$method = $class->addMethod('getBy' . Strings::firstUpper($fieldName));
			$method->addParameter($fieldName)
				->setType($fieldType === 'uuid' ? 'Ramsey\Uuid\UuidInterface' : $fieldType);
			$method->setReturnType($namespaceString . '\\' . $entityName);
			$method->setVisibility('public')
				->addComment('@throws ' . $entityName . 'NotFoundException');
			foreach ($this->createGetByBody($entityName, $fieldName) as $code) {
				$method->addBody($code);
			}
		}

		foreach ($getAllByMethods as $fieldName => $fieldType) {
			$method = $class->addMethod('getAllBy' . Strings::firstUpper($fieldName));
			$method->addParameter($fieldName)
				->setType(Strings::contains(strtolower($fieldType), 'uuid') ? 'Ramsey\Uuid\UuidInterface' : $fieldType);
			$method->setReturnType('array');
			$method->setVisibility('public')
				->addComment('@return ' . $entityName . '[]');
			foreach ($this->createGetAllByBody($entityName, $fieldName) as $code) {
				$method->addBody($code);
			}
		}

		if ($createGetAllMethod) {
			$class->addMethod('getAll')
				->setReturnType('array')
				->setVisibility('public')
				->addComment('@return ' . $entityName . '[]')
				->addBody('return $this->getQueryBuilderForAll()->getQuery()->execute();');
		}

		$class->addMethod('getQueryBuilderForAll')
			->setReturnType('Doctrine\ORM\QueryBuilder')
			->setVisibility('private')
			->addBody('return $this->getRepository()->createQueryBuilder(\'e\');');

		$namespace->add($class);

		return preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\r\n\r\n", (string) $file);
	}

	private function createGetByBody(string $entityName, string $fieldName): array
	{
		$code = [];

		$code[] = '/** @var ' . $entityName . ' $' . Strings::firstLower($entityName) . ' */';
		$code[] = '$' . Strings::firstLower($entityName) . ' = $this->getRepository()->findOneBy([';
		$code[] = '	\'' . $fieldName . '\' => $' . $fieldName;
		$code[] = '';
		$code[] = 'if ($' . Strings::firstLower($entityName) . ' === null) {';
		$code[] = '	throw new ' . $entityName . 'NotFoundException(\'' . $entityName . ' with ' . $fieldName . ' "\' . $' . Strings::firstLower($fieldName) . ' . \'" not found.\');';
		$code[] = '}';
		$code[] = '';
		$code[] = 'return $' . Strings::firstLower($entityName) . ';';

		return $code;
	}

	private function createGetAllByBody(string $entityName, string $fieldName): array
	{
		$code = [];

		$code[] = 'return $this->getRepository()->findBy([';
		$code[] = '	\'' . $fieldName . '\' => $' . $fieldName;
		$code[] = ']);';

		return $code;
	}
}
