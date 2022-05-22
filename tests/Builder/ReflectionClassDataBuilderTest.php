<?php

namespace Walnut\Lib\DataType\Builder;

use PHPUnit\Framework\TestCase;
use Walnut\Lib\DataType\ClassData;
use Walnut\Lib\DataType\Importer\Builder\ReflectionClassDataBuilder;

/**
 * @package Walnut\Lib\DataType
 */
final class ReflectionClassDataBuilderTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();

		$this->builder = new ReflectionClassDataBuilder;
	}

	public function testBuilder(): void {
		$values = [
			1,
			3.14,
			'TEST',
			true,
			[],
			(object)[],
			(object)[],
			null,
			0,
			'x'
		];
		$classData = $this->builder->buildForClass((new class(...$values) {
			public int $defaultProperty = 0;
			public $x;
			public function __construct(
				public readonly int $intValue,
				public readonly float $floatValue,
				public readonly string $stringValue,
				public readonly bool $boolValue,
				public readonly array $arrayValue,
				public readonly object $objectValue,
				public readonly \stdClass $stdClassValue,
				public readonly mixed $mixedValue,
				public readonly int|string $intOrStringValue,
				mixed $x,
				int $defaultProperty = 0,
				public readonly int $defaultValue = 0,
			) {
				$this->defaultProperty = $defaultProperty;
				$this->x = $x;
			}
		})::class);
		$this->assertInstanceOf(ClassData::class, $classData);
	}
}
