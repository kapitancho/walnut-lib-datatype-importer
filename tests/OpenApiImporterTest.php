<?php

namespace Walnut\Lib\DataType\Importer;

use PHPUnit\Framework\TestCase;
use Walnut\Lib\DataType\AnyData;
use Walnut\Lib\DataType\Exception\InvalidData;
use Walnut\Lib\DataType\Exception\ObjectType\UnsupportedObjectPropertyFound;
use Walnut\Lib\DataType\IntegerData;
use Walnut\Lib\DataType\ObjectData;
use Walnut\Lib\DataType\StringData;

#[ObjectData(additionalPropertiesIn: 'other', required: ['optionalInt'])]
final class MockImporterDefaultModel {
	public function __construct(
		public /*readonly*/ bool $bool,
		public /*readonly*/ int $int,
		public /*readonly*/ ?int $optionalInt,
		public /*readonly*/ float $float,
		public /*readonly*/ string $string,
		public /*readonly*/ array $array,
		public /*readonly*/ object $object,
		#[AnyData]
		public /*readonly*/ array $other
	) {}
}

final class MockImporterModel {
    public function __construct(
        #[IntegerData(minimum: 1, maximum: 999999)]
        public /*readonly*/ int $numPages,

        #[StringData(minLength: 1, maxLength: 100)]
        public /*readonly*/ string $authorName,

        public /*readonly*/ MockImporterDefaultModel $defaultModel,


    ) {}
}


final class OpenApiImporterText extends TestCase {

	private function getImporter(): OpenApiImporter {
		return new OpenApiImporter(
			new OpenApiReflector
		);
	}

	private function getSource(): array {
		return [
			'numPages' => 200,
			'authorName' => 'Author',
			'defaultModel' => [
				'bool' => true,
				'int' => 1,
				'optionalInt' => null,
				'float' => 3.14,
				'string' => 'text',
				'array' => [1, 'value'],
				'object' => (object)['property' => 'value'],
				'additional' => 5
			]
		];
	}

	public function testOk(): void {
		$source = $this->getSource();
		$importer = $this->getImporter();
		$bookObject = $importer->import($source, MockImporterModel::class);
		$importer->validate($bookObject); //ok

		$this->assertEquals(200, $bookObject->numPages);
		$this->assertEquals('Author', $bookObject->authorName);
		//$this->assertEquals(5, $bookObject->defaultModel->other['additional']);
	}

	public function testInvalidValue(): void {
		$this->expectException(InvalidData::class);
		$importer = $this->getImporter();
		$importer->import([], MockImporterModel::class);
	}

	public function testUnsupportedObjectPropertyFound(): void {
		$this->expectException(InvalidData::class);
		$source = $this->getSource();
		$source['extra'] = 1;
		$importer = $this->getImporter();
		$importer->import($source, MockImporterModel::class);
	}

	public function testRequiredObjectPropertyMissing(): void {
		$this->expectException(InvalidData::class);
		$source = $this->getSource();
		unset($source['defaultModel']['optionalInt']);
		$importer = $this->getImporter();
		$importer->import($source, MockImporterModel::class);
	}

}