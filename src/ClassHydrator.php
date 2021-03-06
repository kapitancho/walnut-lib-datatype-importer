<?php

namespace Walnut\Lib\DataType\Importer;

use Walnut\Lib\DataType\Exception\InvalidValue;

/**
 * @package Walnut\Lib\DataType
 */
interface ClassHydrator {
	/**
	 * @template T of object
	 * @param null|string|float|int|bool|array|object $value
	 * @param class-string<T> $className
	 * @return T
	 * @throws InvalidValue
	 */
	public function importValue(
		null|string|float|int|bool|array|object $value,
		string $className
	): object;
}
