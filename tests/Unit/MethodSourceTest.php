<?php declare(strict_types = 1);

namespace Tests\Orisai\SourceMap\Unit;

use Closure;
use Generator;
use Orisai\SourceMap\ClassSource;
use Orisai\SourceMap\Exception\InvalidSource;
use Orisai\SourceMap\MethodSource;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use Tests\Orisai\SourceMap\Doubles\AnnotatedReflectedClass;
use function preg_replace;
use function serialize;
use function unserialize;
use const PHP_EOL;

final class MethodSourceTest extends TestCase
{

	public function test(): void
	{
		$class = AnnotatedReflectedClass::class;
		$reflector = new ReflectionMethod($class, 'test');
		$classSource = new ClassSource(new ReflectionClass($class));

		$source = new MethodSource($reflector);

		self::assertTrue($source->isValid());
		self::assertEquals($classSource, $source->getClass());
		self::assertSame($reflector, $source->getReflector());

		self::assertSame("{$class}->test()", $source->toString());
		self::assertSame($source->toString(), (string) $source);
		self::assertSame("{$class}->test(foo)", $source->toString(['foo']));
		self::assertSame("{$class}->test(foo, bar)", $source->toString(['foo', 'bar']));

		self::assertEquals($source->getClass()->getLastChange(), $source->getLastChange());
		self::assertGreaterThanOrEqual(2_023, (int) $source->getLastChange()->format('Y'));

		self::assertEquals($source, unserialize(serialize($source)));
	}

	public function testStatic(): void
	{
		$class = AnnotatedReflectedClass::class;
		$reflector = new ReflectionMethod($class, 'staticTest');

		$source = new MethodSource($reflector);

		self::assertSame("$class::staticTest()", $source->toString());
		self::assertSame($source->toString(), (string) $source);
		self::assertSame("$class::staticTest(foo)", $source->toString(['foo']));
		self::assertSame("$class::staticTest(foo, bar)", $source->toString(['foo', 'bar']));
	}

	public function testSerializationBC(): void
	{
		// phpcs:ignore SlevomatCodingStandard.Files.LineLength
		$serialized = 'O:29:"Orisai\SourceMap\MethodSource":2:{s:5:"class";s:54:"Tests\Orisai\SourceMap\Doubles\AnnotatedReflectedClass";s:6:"method";s:4:"test";}';

		$class = AnnotatedReflectedClass::class;
		$reflector = new ReflectionMethod($class, 'test');
		$classSource = new ClassSource(new ReflectionClass($class));

		$source = unserialize($serialized);
		self::assertInstanceOf(MethodSource::class, $source);

		self::assertTrue($source->isValid());
		self::assertEquals($classSource, $source->getClass());
		self::assertEquals($reflector, $source->getReflector());

		self::assertSame("{$class}->test()", $source->toString());
		self::assertSame("{$class}->test(foo)", $source->toString(['foo']));
		self::assertSame("{$class}->test(foo, bar)", $source->toString(['foo', 'bar']));

		self::assertEquals($source, unserialize(serialize($source)));
	}

	/**
	 * @dataProvider provideUnSerializationFailure
	 */
	public function testUnSerializationFailure(Closure $call): void
	{
		// phpcs:ignore SlevomatCodingStandard.Files.LineLength
		$serialized = 'O:29:"Orisai\SourceMap\MethodSource":2:{s:5:"class";s:41:"Tests\Orisai\SourceMap\Doubles\EmptyClass";s:6:"method";s:4:"test";}';
		$source = unserialize($serialized);
		self::assertInstanceOf(MethodSource::class, $source);

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
Method Tests\Orisai\SourceMap\Doubles\EmptyClass::test() does not exist
MSG,
			preg_replace('~\R~u', PHP_EOL, $e->getMessage()),
		);
		self::assertInstanceOf(ReflectionException::class, $e->getPrevious());
		self::assertSame($source, $e->getSource());
	}

	public function provideUnSerializationFailure(): Generator
	{
		yield [
			static fn (MethodSource $source) => $source->getClass(),
		];

		yield [
			static fn (MethodSource $source) => $source->getReflector(),
		];

		yield [
			static fn (MethodSource $source) => $source->toString(),
		];

		yield [
			static fn (MethodSource $source) => serialize($source),
		];
	}

}
