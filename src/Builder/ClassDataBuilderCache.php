<?php

namespace Walnut\Lib\DataType\Importer\Builder;

use Walnut\Lib\DataType\ClassData;
use Walnut\Lib\DataType\CustomClassData;
use Walnut\Lib\DataType\EnumData;
use Walnut\Lib\DataType\WrapperClassData;

/**
 * @package Walnut\Lib\DataType
 */
final class ClassDataBuilderCache implements ClassDataBuilder {
	/**
	 * @var array<class-string, ClassData|EnumData|WrapperClassData|CustomClassData>
	 */
	private array $cache = [];
	public function __construct(private readonly ClassDataBuilder $builder) {}

	/**
	 * @template T of object
	 * @param class-string<T> $className
	 * @return ClassData<T>|EnumData<T>|WrapperClassData<T>|CustomClassData<T>
	 */
	public function buildForClass(string $className): ClassData|EnumData|WrapperClassData|CustomClassData {
		/**
		 * @var ClassData<T>|EnumData<T>|WrapperClassData<T>|CustomClassData<T>
		 */
		return $this->cache[$className] ??= $this->builder->buildForClass($className);
	}
}