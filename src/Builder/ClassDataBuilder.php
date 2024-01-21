<?php

namespace Walnut\Lib\DataType\Importer\Builder;

use Walnut\Lib\DataType\ClassData;
use Walnut\Lib\DataType\CustomClassData;
use Walnut\Lib\DataType\EnumData;
use Walnut\Lib\DataType\WrapperClassData;

/**
 * @package Walnut\Lib\DataType
 */
interface ClassDataBuilder {
	/**
	 * @template T
	 * @param class-string<T> $className
	 * @return ClassData<T>|EnumData<T>|WrapperClassData<T>|CustomClassData<T>
	 */
	public function buildForClass(string $className): ClassData|EnumData|WrapperClassData|CustomClassData;
}