<?php

namespace Walnut\Lib\DataType\Builder;

use PHPUnit\Framework\TestCase;
use Walnut\Lib\DataType\ClassData;
use Walnut\Lib\DataType\Importer\Builder\ClassDataBuilder;
use Walnut\Lib\DataType\Importer\Builder\ClassDataBuilderCache;

/**
 * @package Walnut\Lib\DataType
 */
final class ClassDataBuilderCacheTest extends TestCase {

	public function testCaching(): void {
		$classDataBuilder = $this->createMock(ClassDataBuilder::class);
		$classDataBuilder->expects($this->once())->method('buildForClass')
			->willReturn(new ClassData(\stdClass::class));
		$builder = new ClassDataBuilderCache($classDataBuilder);
		$builder->buildForClass(\stdClass::class);
		$builder->buildForClass(\stdClass::class);
	}
}
