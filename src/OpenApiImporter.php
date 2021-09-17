<?php

namespace Walnut\Lib\DataType\Importer;

use JsonException;
use ReflectionException;
use Walnut\Lib\DataType\DataImporter;
use Walnut\Lib\DataType\Exception\DataImporterException;
use Walnut\Lib\DataType\ObjectData;
use Walnut\Lib\DataType\Exception\ObjectType\RequiredObjectPropertyMissing;
use Walnut\Lib\DataType\Exception\ObjectType\UnsupportedObjectPropertyFound;
use Walnut\Lib\DataType\Exception\InvalidValue;
use Walnut\Lib\DataType\RefValue;
use Walnut\Lib\DataType\ValueValidator;
use Walnut\Lib\DataType\Exception\InvalidData;

final class OpenApiImporter implements DataImporter {

	public function __construct(
		private /*readonly*/ OpenApiReflector $reflector
	) { }

	/**
	 * @param ValueValidator[] $propertyValueDefinitions
	 * @param int|float|bool|string|array|object|null $value
	 * @param string $currentPath
	 * @return int|float|bool|string|array|object|null
	 * @throws DataImporterException|InvalidData
	 */
	private function importValue(array $propertyValueDefinitions, int|float|bool|string|array|object|null $value, string $currentPath): int|float|bool|null|string|array|object {
		try {
			$propertyValueDefinition = array_shift($propertyValueDefinitions);
			$propertyValueDefinition?->validateValue($value);

			if (($propertyValueDefinition instanceof RefValue) && (
				is_object($value) || is_array($value)
			)) {
				return $this->importObject((object)$value, $propertyValueDefinition->targetClass, $currentPath);
			}
			if (is_array($value)) {
				$result = [];
				/**
				 * @var int $seq
				 * @var int|float|bool|null|string|array|object $item
				 */
				foreach($value as $seq => $item) {
					$result[] = $this->importValue($propertyValueDefinitions, $item, $currentPath . "[$seq]");
				}
				return $result;
			}
		} catch (InvalidValue $ex) {
			throw new InvalidData($currentPath, $value,$ex);
			// @codeCoverageIgnoreStart
		} catch (ReflectionException $ex) {
			throw new DataImporterException($ex->getMessage());
			// @codeCoverageIgnoreEnd
		}
		return $value;
	}

	/**
	 * @template T
	 * @param object $object
	 * @param class-string<T> $className
	 * @param string $currentPath
	 * @return T
	 * @throws InvalidData
	 * @throws ReflectionException
	 */
	public function importObject(object $object, string $className, string $currentPath): object {
		$reflector = $this->reflector->getClassReflector($className);
		return $reflector->instantiate(
			$this->getObjectArgs(
				$object,
				$reflector->getObjectData(),
				$reflector->getAllPropertyData(),
				$currentPath
			)
		);
	}

	/**
	 * @param object $object
	 * @param ObjectData $objectData
	 * @param array<string, ValueValidator[]> $propertyData
	 * @param string $currentPath
	 * @return array
	 * @throws InvalidData
	 * @throws ReflectionException
	 */
	private function getObjectArgs(
		object $object,
		ObjectData $objectData,
		array $propertyData,
		string $currentPath
	): array {
		try {
			$objectData->validateValue($object);
			$requiredProperties = $objectData->required;

			/**
			 * @var array<array-key, int|float|bool|string|array|object|null> $args
			 */
			$args = [];
			$additionalArgs = [];

			$additionalPropertiesIn = $objectData->additionalPropertiesIn;
			$additionalPropertiesData = $additionalPropertiesIn ?
				$propertyData[$additionalPropertiesIn] ?? [] : [];

			/**
			 * @var int|float|bool|null|string|array|object $value
			 */
			foreach(get_object_vars($object) as $prop => $value) {
				if ($prop === $additionalPropertiesIn) {
					continue;
				}
				$isOwnProperty = array_key_exists($prop, $propertyData);
				if (!$additionalPropertiesIn && !$isOwnProperty) {
					throw new UnsupportedObjectPropertyFound($prop);
				}
				$propertyValueDefinitions = $isOwnProperty ? $propertyData[$prop] : $additionalPropertiesData;
				$result = $this->importValue(
					$propertyValueDefinitions, $value, "$currentPath.$prop"
				);
				if ($isOwnProperty) {
					$args[$prop] = $result;
				} else {
					$additionalArgs[$prop] = $result;
				}
			}
			foreach($propertyData as $propertyName => $propertyDataItem) {
				if ($propertyName === $additionalPropertiesIn) {
					continue;
				}
				if (!array_key_exists($propertyName, $args)) {
					if (in_array($propertyName, $requiredProperties, true)) {
						// @codeCoverageIgnoreStart
						throw new RequiredObjectPropertyMissing($propertyName);
						// @codeCoverageIgnoreEnd
					}
					$args[$propertyName] = $this->importValue(
						$propertyDataItem, null, "$currentPath.$propertyName"
					);
				}
			}
			if ($additionalPropertiesIn) {
				$args[$additionalPropertiesIn] = $additionalArgs;
			}

		} catch (InvalidValue $ex) {
			throw new InvalidData($currentPath, $object, $ex);
		}
		return $args;
	}

	/**
	 * @template T
	 * @param array|object $object
	 * @param class-string<T> $className
	 * @return T
	 * @throws ReflectionException|InvalidData
	 */
	public function import(array|object $object, string $className): object {
		return $this->importObject((object)$object, $className, '');
	}

	/**
	 * @param object $object
	 * @throws InvalidData
	 * @throws DataImporterException|InvalidData
	 */
	public function validate(object $object): void {
		try {
			/**
			 * @var object $reEncoded
			 */
			$reEncoded = json_decode(json_encode($object), flags: JSON_THROW_ON_ERROR);
			$this->import($reEncoded, get_class($object));
			// @codeCoverageIgnoreStart
		} catch (JsonException|ReflectionException $ex) {
			throw new DataImporterException($ex->getMessage());
			// @codeCoverageIgnoreEnd
		}
	}

}