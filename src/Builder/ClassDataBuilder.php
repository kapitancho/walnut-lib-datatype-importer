<?php

namespace Walnut\Lib\DataType\Importer\Builder;

use Walnut\Lib\DataType\ClassData;

/**
 * @package Walnut\Lib\DataType
 */
interface ClassDataBuilder {
	/**
	 * @template T
	 * @param class-string<T> $className
	 * @return ClassData<T>
	 */
	public function buildForClass(string $className): ClassData;
}