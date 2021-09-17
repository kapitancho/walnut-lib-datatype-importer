<?php

namespace Walnut\Lib\DataType\Importer;

use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionProperty;
use Walnut\Lib\DataType\{
	ArrayData, IntegerData, NumberData, ObjectData, RefValue, StringData, BooleanData, ValueValidator
};

/**
 * @template T of object
 */
final class OpenApiClassReflector {

	/**
	 * @var ReflectionClass
	 */
	private /*readonly*/ ReflectionClass $reflectionClass;

	/**
	 * @param class-string<T> $className
	 * @throws ReflectionException
	 */
	public function __construct(
		string $className
	) {
		$this->reflectionClass = new ReflectionClass($className);
	}

	/**
	 * @param ReflectionProperty $reflectionItem
	 * @return ValueValidator[]
	 */
	private function getPropertyValueDefinitions(ReflectionProperty $reflectionItem): array {
		$valueDefinitions = [];
		foreach($reflectionItem->getAttributes(ValueValidator::class, ReflectionAttribute::IS_INSTANCEOF) as $valueDef) {
			$valueDefinitions[] = $valueDef->newInstance();
		}
		if (!$valueDefinitions) {
			$t = $reflectionItem->getType();
			if ($t instanceof ReflectionNamedType) {
				if ($t->isBuiltin()) {
					$autoDef = match($t->getName()) {
						'int' => new IntegerData(nullable: $t->allowsNull()),
						'float' => new NumberData(nullable: $t->allowsNull()),
						'string' => new StringData(nullable: $t->allowsNull()),
						'bool' => new BooleanData(nullable: $t->allowsNull()),
						'array' => new ArrayData(nullable: $t->allowsNull()),
						default => null
					};
					if ($autoDef) {
						$valueDefinitions[] = $autoDef;
					}
				} else {
					/**
					 * @var class-string $type
					 */
					$type = $t->getName();
					$valueDefinitions[] = new RefValue($type, nullable: $t->allowsNull());
				}
			}
		}
		return $valueDefinitions;
	}

	/**
	 * @return ObjectData
	 */
	public function getObjectData(): ObjectData {
		$objectData = $this->reflectionClass->getAttributes(
			ObjectData::class, ReflectionAttribute::IS_INSTANCEOF
		)[0] ?? null;
		/* *
		 * @var ?ObjectData $result
		 */
		$result = $objectData?->newInstance();
		return $result ?? new ObjectData;
	}

	/**
	 * @return array<string, ValueValidator[]>
	 */
	public function getAllPropertyData(): array {
		$result = [];
		foreach($this->reflectionClass->getProperties() as $property) {
			$result[$property->getName()] = $this->getPropertyValueDefinitions($property);
		}
		return $result;
	}

	/**
	 * @param array $args
	 * @return T
	 * @throws ReflectionException
	 */
	public function instantiate(array $args): object {
		/**
		 * @var T
		 */
		return $this->reflectionClass->newInstance(...$args);
	}

}