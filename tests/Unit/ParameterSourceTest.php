<?php declare(strict_types = 1);

namespace Tests\Orisai\SourceMap\Unit;

use Closure;
use Generator;
use Orisai\SourceMap\Exception\InvalidSource;
use Orisai\SourceMap\FunctionSource;
use Orisai\SourceMap\MethodSource;
use Orisai\SourceMap\ParameterSource;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionParameter;
use Tests\Orisai\SourceMap\Doubles\AnnotatedReflectedClass;
use function preg_replace;
use function serialize;
use function unserialize;
use const PHP_EOL;

final class ParameterSourceTest extends TestCase
{

	public static function setUpBeforeClass(): void
	{
		parent::setUpBeforeClass();

		require_once __DIR__ . '/../Doubles/testFunction.php';
	}

	public function test(): void
	{
		$class = AnnotatedReflectedClass::class;
		$reflector = new ReflectionParameter([$class, 'test'], 'test');
		$methodSource = new MethodSource(new ReflectionMethod($class, 'test'));

		$source = new ParameterSource($reflector);

		self::assertTrue($source->isValid());
		self::assertEquals($methodSource, $source->getFunction());
		self::assertSame($reflector, $source->getReflector());

		self::assertSame("{$class}->test(test)", $source->toString());
		self::assertSame($source->toString(), (string) $source);

		self::assertEquals($source->getFunction()->getLastChange(), $source->getLastChange());
		self::assertGreaterThanOrEqual(2_023, (int) $source->getLastChange()->format('Y'));

		self::assertEquals($source, unserialize(serialize($source)));
	}

	public function testFunction(): void
	{
		$function = 'Tests\Orisai\SourceMap\Doubles\test';
		$reflector = new ReflectionParameter($function, 'test');
		$functionSource = new FunctionSource(new ReflectionFunction($function));

		$source = new ParameterSource($reflector);

		self::assertTrue($source->isValid());
		self::assertEquals($functionSource, $source->getFunction());
		self::assertSame($reflector, $source->getReflector());
		self::assertSame("$function(test)", $source->toString());
		self::assertSame($source->toString(), (string) $source);
		self::assertEquals($source, unserialize(serialize($source)));
	}

	public function testSerializationBC(): void
	{
		// phpcs:ignore SlevomatCodingStandard.Files.LineLength
		$serialized = 'O:32:"Orisai\SourceMap\ParameterSource":3:{s:5:"class";s:54:"Tests\Orisai\SourceMap\Doubles\AnnotatedReflectedClass";s:8:"function";s:4:"test";s:9:"parameter";s:4:"test";}';

		$class = AnnotatedReflectedClass::class;
		$reflector = new ReflectionParameter([$class, 'test'], 'test');
		$methodSource = new MethodSource(new ReflectionMethod($class, 'test'));

		$source = unserialize($serialized);
		self::assertInstanceOf(ParameterSource::class, $source);

		self::assertTrue($source->isValid());
		self::assertEquals($methodSource, $source->getFunction());
		self::assertEquals($reflector, $source->getReflector());
		self::assertSame("{$class}->test(test)", $source->toString());
		self::assertEquals($source, unserialize(serialize($source)));
	}

	/**
	 * @dataProvider provideUnSerializationFailure
	 */
	public function testUnSerializationFailure(Closure $call): void
	{
		// phpcs:ignore SlevomatCodingStandard.Files.LineLength
		$serialized = 'O:32:"Orisai\SourceMap\ParameterSource":3:{s:5:"class";s:54:"Tests\Orisai\SourceMap\Doubles\AnnotatedReflectedClass";s:8:"function";s:4:"test";s:9:"parameter";s:11:"nonexistent";}';
		$source = unserialize($serialized);
		self::assertInstanceOf(ParameterSource::class, $source);

		self::assertFalse($source->isValid());

		$e = null;
		try {
			$call($source);
		} catch (InvalidSource $e) {
			//  Handled bellow
		}

		self::assertInstanceOf(InvalidSource::class, $e);
		self::assertSame(
			<<<'MSG'
Deserialization failed due to following error:
Parameter Tests\Orisai\SourceMap\Doubles\AnnotatedReflectedClass::test(nonexistent) does not exist
MSG,
			preg_replace('~\R~u', PHP_EOL, $e->getMessage()),
		);
		self::assertInstanceOf(ReflectionException::class, $e->getPrevious());
		self::assertSame($source, $e->getSource());
	}

	public function provideUnSerializationFailure(): Generator
	{
		yield [
			static fn (ParameterSource $source) => $source->getFunction(),
		];

		yield [
			static fn (ParameterSource $source) => $source->getReflector(),
		];

		yield [
			static fn (ParameterSource $source) => $source->toString(),
		];

		yield [
			static fn (ParameterSource $source) => serialize($source),
		];
	}

}
