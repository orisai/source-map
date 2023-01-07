<?php declare(strict_types = 1);

namespace Tests\Orisai\SourceMap\Unit;

use Closure;
use Generator;
use Orisai\Exceptions\Logic\InvalidState;
use Orisai\SourceMap\FunctionSource;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ReflectionFunction;
use function preg_replace;
use function serialize;
use function unserialize;
use const PHP_EOL;

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

	/**
	 * @dataProvider provideUnSerializationFailure
	 */
	public function testUnSerializationFailure(Closure $call): void
	{
		// phpcs:ignore SlevomatCodingStandard.Files.LineLength
		$serialized = 'O:31:"Orisai\SourceMap\FunctionSource":1:{s:8:"function";s:42:"Tests\Orisai\SourceMap\Doubles\nonExistent";}';
		$source = unserialize($serialized);
		self::assertInstanceOf(FunctionSource::class, $source);

		self::assertFalse($source->isValid());

		$e = null;
		try {
			$call($source);
		} catch (InvalidState $e) {
			//  Handled bellow
		}

		self::assertInstanceOf(InvalidState::class, $e);
		self::assertSame(
			<<<'MSG'
Deserialization failed due to following error:
Function Tests\Orisai\SourceMap\Doubles\nonExistent() does not exist
MSG,
			preg_replace('~\R~u', PHP_EOL, $e->getMessage()),
		);
		self::assertInstanceOf(ReflectionException::class, $e->getPrevious());
	}

	public function provideUnSerializationFailure(): Generator
	{
		yield [
			static fn (FunctionSource $source) => $source->getReflector(),
		];

		yield [
			static fn (FunctionSource $source) => $source->toString(),
		];

		yield [
			static fn (FunctionSource $source) => serialize($source),
		];
	}

}
