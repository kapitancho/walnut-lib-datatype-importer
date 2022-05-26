<?php

namespace Walnut\Lib\DataType\Builder;

use PHPUnit\Framework\TestCase;
use Walnut\Lib\DataType\ClassData;
use Walnut\Lib\DataType\Importer\Builder\ReflectionClassDataBuilder;
use Walnut\Lib\DataType\EnumDataType;

enum ReflectionClassDataBuilderTestIntEnum: int { case A = 1; case C = 3; }
enum ReflectionClassDataBuilderTestStringEnum: string { case A = 'z'; case C = 'x'; }
enum ReflectionClassDataBuilderTestUnitEnum { case A; case C; }

/**
 * @package Walnut\Lib\DataType
 */
final class ReflectionClassDataBuilderTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();

		$this->builder = new ReflectionClassDataBuilder;
	}

	public function testIntEnum(): void {
		$intEnum = $this->builder->buildForClass(ReflectionClassDataBuilderTestIntEnum::class);
		$this->assertEquals(EnumDataType::INT, $intEnum->type);
		$this->assertEquals([1, 3], $intEnum->values);
		$this->assertEquals(ReflectionClassDataBuilderTestIntEnum::C, $intEnum->importValue(3));
	}

	public function testStringEnum(): void {
		$stringEnum = $this->builder->buildForClass(ReflectionClassDataBuilderTestStringEnum::class);
		$this->assertEquals(EnumDataType::STRING, $stringEnum->type);
		$this->assertEquals(['z', 'x'], $stringEnum->values);
		$this->assertEquals(ReflectionClassDataBuilderTestStringEnum::C, $stringEnum->importValue('x'));
	}

	public function testUnitEnum(): void {
		$unitEnum = $this->builder->buildForClass(ReflectionClassDataBuilderTestUnitEnum::class);
		$this->assertEquals(EnumDataType::UNIT, $unitEnum->type);
		$this->assertEquals(['A', 'C'], $unitEnum->values);
		$this->assertEquals(ReflectionClassDataBuilderTestUnitEnum::C, $unitEnum->importValue('C'));
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
