<?php

namespace Walnut\Lib\DataType;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Walnut\Lib\DataType\Exception\InvalidData;
use Walnut\Lib\DataType\Exception\InvalidValueType;
use Walnut\Lib\DataType\Importer\Builder\ClassDataBuilder;
use Walnut\Lib\DataType\Importer\DefaultImporter;

/**
 * @package Walnut\Lib\DataType
 */
final class DefaultImporterTest extends TestCase {

	private MockObject $classDataBuilder;
	private DefaultImporter $importer;

	protected function setUp(): void {
		parent::setUp();

		$this->classDataBuilder = $this->createMock(ClassDataBuilder::class);
		$this->importer = new DefaultImporter($this->classDataBuilder, 'IMPORT-PATH');
	}

	public function testImportNestedValue(): void {
		$mock = $this->createMock(CompositeValue::class);
		$mock->method('importValue')->willReturn('TEST');

		$this->assertEquals('TEST', $this->importer->importNestedValue(
			'WRONG-TYPE',
			$mock,
			'key'
		));
	}

	public function testInvalidValue(): void {
		$this->expectException(InvalidData::class);

		$mock = $this->createMock(CompositeValue::class);
		$mock->method('importValue')->willThrowException(new InvalidValueType("a", "b"));

		$this->importer->importNestedValue(
			'WRONG-TYPE',
			$mock
		);
	}

	public function testImportRefValue(): void {
		$this->classDataBuilder->method('buildForClass')->willReturn(new ClassData(\stdClass::class));

		$this->assertEquals(new \stdClass, $this->importer->importRefValue(
			[],
			\stdClass::class
		));
	}
}
