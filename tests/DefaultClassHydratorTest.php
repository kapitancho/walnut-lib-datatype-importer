<?php

namespace Walnut\Lib\DataType\Hydrator;

use PHPUnit\Framework\TestCase;
use Walnut\Lib\DataType\ClassRefHydrator;
use Walnut\Lib\DataType\Importer\DefaultClassHydrator;

/**
 * @package Walnut\Lib\DataType
 */
final class DefaultClassHydratorTest extends TestCase {

	public function testImportValue(): void {
		$refValueImporter = $this->createMock(ClassRefHydrator::class);
		$hydrator = new DefaultClassHydrator($refValueImporter);
		$this->assertIsObject($hydrator->importValue([
			'a' => 1,
			'b' => 2
		], \stdClass::class));
	}
}
