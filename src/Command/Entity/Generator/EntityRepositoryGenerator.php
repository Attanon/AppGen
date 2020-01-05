<?php

declare(strict_types=1);

namespace Archette\AppGen\Command\Entity\Generator;

use Archette\AppGen\Command\Entity\CreateEntityInput;
use Archette\AppGen\Config\AppGenConfig;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\Type;
use Nette\Utils\Strings;

class EntityRepositoryGenerator
{
	private AppGenConfig $config;

	public function __construct(
		AppGenConfig $config
	) {
		$this->config = $config;
	}

	public function create(CreateEntityInput $input): string
	{
		$file = new PhpFile();

		$file->setStrictTypes();

		$namespace = $file->addNamespace($input->getNamespace());
		if (in_array('DateTime', array_merge(array_values($input->getGetByMethods()), array_values($input->getGetAllByMethods())))) {
			$namespace->addUse('DateTime');
		}
		$namespace->addUse('Doctrine\ORM\EntityManagerInterface');
		$namespace->addUse('Doctrine\ORM\EntityRepository');
		$namespace->addUse('Doctrine\ORM\QueryBuilder');
		if (Strings::contains($this->config->entity->idType, 'uuid')) {
			$namespace->addUse('Ramsey\Uuid\UuidInterface');
		}
		$namespace->addUse($input->getNotFoundExceptionClass(true));

		$class = new ClassType($input->getRepositoryClass());
		$class->setAbstract();

		$class->addProperty('entityManager')
			->setVisibility(ClassType::VISIBILITY_PRIVATE)
			->setType('Doctrine\ORM\EntityManagerInterface');

		$constructor = $class->addMethod('__construct');
		$constructor->addParameter('entityManager')
			->setType('Doctrine\ORM\EntityManagerInterface');
		$constructor->addBody('$this->entityManager = $entityManager;');

		$class->addMethod('getRepository')
			->setVisibility(ClassType::VISIBILITY_PRIVATE)
			->addComment('@return EntityRepository|ObjectRepository')
			->addBody('return $this->entityManager->getRepository(' . $input->getEntityClass() . '::class);');

		$get = $class->addMethod('get');
		$get->addParameter('id')
			->setType(Strings::contains($this->config->entity->idType, 'uuid') ? 'Ramsey\Uuid\UuidInterface' : Type::INT);
		$get->setReturnType($input->getEntityClass(true));
		$get->setVisibility(ClassType::VISIBILITY_PUBLIC)
			->addComment('@throws ' . $input->getNotFoundExceptionClass());

		foreach ($this->createGetByBody($input->getEntityClass(), 'id') as $code) {
			$get->addBody($code);
		}

		foreach ($input->getGetByMethods() as $fieldName => $fieldType) {
			$method = $class->addMethod('getBy' . Strings::firstUpper($fieldName));
			$method->addParameter($fieldName)
				->setType(Strings::contains(strtolower($fieldType), 'uuid') ? 'Ramsey\Uuid\UuidInterface' : $fieldType);
			$method->setReturnType($input->getEntityClass(true));
			$method->setVisibility(ClassType::VISIBILITY_PUBLIC)
				->addComment('@throws ' . $input->getNotFoundExceptionClass());
			foreach ($this->createGetByBody($input->getEntityClass(), $fieldName) as $code) {
				$method->addBody($code);
			}
		}

		foreach ($input->getGetAllByMethods() as $fieldName => $fieldType) {
			$method = $class->addMethod('getAllBy' . Strings::firstUpper($fieldName));
			$method->addParameter($fieldName)
				->setType(Strings::contains(strtolower($fieldType), 'uuid') ? 'Ramsey\Uuid\UuidInterface' : $fieldType);
			$method->setReturnType(Type::ARRAY);
			$method->setVisibility(ClassType::VISIBILITY_PUBLIC)
				->addComment('@return ' . $input->getEntityClass() . '[]');
			foreach ($this->createGetAllByBody($input->getEntityClass(), $fieldName) as $code) {
				$method->addBody($code);
			}
		}

		if ($input->isCreateGetAllMethod()) {
			$class->addMethod('getAll')
				->setReturnType(Type::ARRAY)
				->setVisibility(ClassType::VISIBILITY_PUBLIC)
				->addComment('@return ' . $input->getEntityClass() . '[]')
				->addBody('return $this->getQueryBuilderForAll()->getQuery()->execute();');
		}

		$class->addMethod('getQueryBuilderForAll')
			->setReturnType('Doctrine\ORM\QueryBuilder')
			->setVisibility(ClassType::VISIBILITY_PRIVATE)
			->addBody('return $this->getRepository()->createQueryBuilder(\'e\');');

		$namespace->add($class);

		return (string) $file;
	}

	private function createGetByBody(string $entityName, string $fieldName): array
	{
		$code = [];

		$code[] = '/** @var ' . $entityName . ' $' . Strings::firstLower($entityName) . ' */';
		$code[] = '$' . Strings::firstLower($entityName) . ' = $this->getRepository()->findOneBy([';
		$code[] = '	\'' . $fieldName . '\' => $' . $fieldName;
		$code[] = ']);';
		$code[] = '';
		$code[] = 'if ($' . Strings::firstLower($entityName) . ' === null) {';
		$code[] = '	throw new ' . $entityName . 'NotFoundException(sprintf(\'' . $entityName . ' with ' . $fieldName . ' "%s" not found.\', $' . Strings::firstLower($fieldName) . '));';
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
