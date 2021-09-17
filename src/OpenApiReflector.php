<?php

namespace Walnut\Lib\DataType\Importer;

use ReflectionException;

final class OpenApiReflector {
	/**
	 * @template T
	 * @param class-string<T> $className
	 * @return OpenApiClassReflector<T>
	 * @throws ReflectionException
	 */
	public function getClassReflector(string $className): OpenApiClassReflector {
		return new OpenApiClassReflector($className);
	}
}