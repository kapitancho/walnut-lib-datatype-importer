<?php

namespace Walnut\Lib\DataType\Importer;

use Walnut\Lib\DataType\ClassRef;
use Walnut\Lib\DataType\ClassRefHydrator;
use Walnut\Lib\DataType\CompositeValue;
use Walnut\Lib\DataType\CompositeValueHydrator;
use Walnut\Lib\DataType\DirectValue;
use Walnut\Lib\DataType\Exception\InvalidData;
use Walnut\Lib\DataType\Exception\InvalidValue;
use Walnut\Lib\DataType\Importer\Builder\ClassDataBuilder;

/**
 * @package Walnut\Lib\DataType
 */
final class DefaultImporter implements CompositeValueHydrator, ClassRefHydrator {

	public function __construct(
		private readonly ClassDataBuilder $classDataBuilder,
		private readonly string $importPath
	) {}

	private function buildImportPath(string|int|null $pathAddition): string {
		$addition = '';
		if (isset($pathAddition)) {
			$addition = is_int($pathAddition) ? "[$pathAddition]" : ".$pathAddition";
		}
		return $this->importPath . $addition;
	}

	/**
	 * @throws InvalidData
	 */
	public function importNestedValue(
		null|string|float|int|bool|array|object $value,
		DirectValue|CompositeValue|ClassRef     $importer,
		string|int|null                         $key = null
	): null|string|float|int|bool|array|object {
		$currentPath = $this->buildImportPath($key);
		try {
			return $importer->importValue($value,
				new self($this->classDataBuilder, $currentPath));
		} catch (InvalidValue $ex) {
			throw new InvalidData($currentPath, $value,$ex);
		}
	}

	/**
	 * @template T of object
	 * @param string|float|int|bool|array|object|null $value
	 * @param class-string<T> $targetClass
	 * @return T
	 * @throws InvalidData
	 */
	public function importRefValue(
		null|string|float|int|bool|array|object $value, string $targetClass
	): object {
		/**
		 * @var T
		 */
		return $this->importNestedValue($value, $this->classDataBuilder->buildForClass($targetClass));
	}
}



