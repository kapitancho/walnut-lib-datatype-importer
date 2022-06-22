<?php

namespace Walnut\Lib\DataType\Importer;

use Walnut\Lib\DataType\ClassRefHydrator;

/**
 * @package Walnut\Lib\DataType
 */
final class DefaultClassHydrator implements ClassHydrator {
	public function __construct(
		private readonly ClassRefHydrator $refValueImporter
	) {}

	/**
	 * @template T of object
	 * @param null|string|float|int|bool|array|object $value
	 * @param class-string<T> $className
	 * @return T
	 */
	public function importValue(
		null|string|float|int|bool|array|object $value,
		string $className
	): object {
		return $this->refValueImporter->importRefValue($value, $className);
	}
}
