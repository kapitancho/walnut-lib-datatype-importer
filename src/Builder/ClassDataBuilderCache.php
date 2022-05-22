<?php

namespace Walnut\Lib\DataType\Importer\Builder;

use Walnut\Lib\DataType\ClassData;

/**
 * @package Walnut\Lib\DataType
 */
final class ClassDataBuilderCache implements ClassDataBuilder {
	/**
	 * @var array<class-string, ClassData>
	 */
	private array $cache = [];
	public function __construct(private readonly ClassDataBuilder $builder) {}

	/**
	 * @template T of object
	 * @param class-string<T> $className
	 * @return ClassData<T>
	 */
	public function buildForClass(string $className): ClassData {
		/**
		 * @var ClassData<T>
		 */
		return $this->cache[$className] ??= $this->builder->buildForClass($className);
	}
}