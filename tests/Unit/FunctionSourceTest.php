<?php declare(strict_types = 1);

namespace Tests\Orisai\SourceMap\Unit;

use Orisai\SourceMap\FunctionSource;
use PHPUnit\Framework\TestCase;
use ReflectionFunction;
use function serialize;
use function unserialize;

final class FunctionSourceTest extends TestCase
{

	public static function setUpBeforeClass(): void
	{
		parent::setUpBeforeClass();

		require_once __DIR__ . '/../Doubles/testFunction.php';
	}

	public function test(): void
	{
		$function = 'Tests\Orisai\SourceMap\Doubles\test';
		$reflector = new ReflectionFunction($function);

		$source = new FunctionSource($reflector);

		self::assertTrue($source->isValid());
		self::assertSame($reflector, $source->getReflector());
		self::assertSame("$function()", $source->toString());
		self::assertEquals($source, unserialize(serialize($source)));
	}

	public function testSerializationBC(): void
	{
		$serialized = 'O:31:"Orisai\SourceMap\FunctionSource":1:{s:8:"function";s:35:"Tests\Orisai\SourceMap\Doubles\test";}';

		$function = 'Tests\Orisai\SourceMap\Doubles\test';
		$reflector = new ReflectionFunction($function);

		$source = unserialize($serialized);
		self::assertInstanceOf(FunctionSource::class, $source);

		self::assertTrue($source->isValid());
		self::assertEquals($reflector, $source->getReflector());
		self::assertSame("$function()", $source->toString());
		self::assertEquals($source, unserialize(serialize($source)));
	}

}
