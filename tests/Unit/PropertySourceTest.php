<?php declare(strict_types = 1);

namespace Tests\Orisai\SourceMap\Unit;

use Closure;
use Generator;
use Orisai\Exceptions\Logic\InvalidState;
use Orisai\SourceMap\ClassSource;
use Orisai\SourceMap\PropertySource;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use Tests\Orisai\SourceMap\Doubles\AnnotatedReflectedClass;
use function preg_replace;
use function serialize;
use function unserialize;
use const PHP_EOL;

final class PropertySourceTest extends TestCase
{

	public function test(): void
	{
		$class = AnnotatedReflectedClass::class;
		$reflector = new ReflectionProperty($class, 'test');
		$classSource = new ClassSource(new ReflectionClass($class));

		$source = new PropertySource($reflector);

		self::assertTrue($source->isValid());
		self::assertEquals($classSource, $source->getClass());
		self::assertSame($reflector, $source->getReflector());
		self::assertSame("$class->\$test", $source->toString());
		self::assertSame($source->toString(), (string) $source);
		self::assertEquals($source, unserialize(serialize($source)));
	}

	public function testStatic(): void
	{
		$class = AnnotatedReflectedClass::class;
		$reflector = new ReflectionProperty($class, 'staticTest');

		$source = new PropertySource($reflector);

		self::assertSame("$class::\$staticTest", $source->toString());
	}

	public function testSerializationBC(): void
	{
		// phpcs:ignore SlevomatCodingStandard.Files.LineLength
		$serialized = 'O:31:"Orisai\SourceMap\PropertySource":2:{s:5:"class";s:54:"Tests\Orisai\SourceMap\Doubles\AnnotatedReflectedClass";s:8:"property";s:4:"test";}';

		$class = AnnotatedReflectedClass::class;
		$reflector = new ReflectionProperty($class, 'test');
		$classSource = new ClassSource(new ReflectionClass($class));

		$source = unserialize($serialized);
		self::assertInstanceOf(PropertySource::class, $source);

		self::assertTrue($source->isValid());
		self::assertEquals($classSource, $source->getClass());
		self::assertEquals($reflector, $source->getReflector());
		self::assertSame("$class->\$test", $source->toString());
		self::assertEquals($source, unserialize(serialize($source)));
	}

	/**
	 * @dataProvider provideUnSerializationFailure
	 */
	public function testUnSerializationFailure(Closure $call): void
	{
		// phpcs:ignore SlevomatCodingStandard.Files.LineLength
		$serialized = 'O:31:"Orisai\SourceMap\PropertySource":2:{s:5:"class";s:41:"Tests\Orisai\SourceMap\Doubles\EmptyClass";s:8:"property";s:4:"test";}';
		$source = unserialize($serialized);
		self::assertInstanceOf(PropertySource::class, $source);

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
Property Tests\Orisai\SourceMap\Doubles\EmptyClass::$test does not exist
MSG,
			preg_replace('~\R~u', PHP_EOL, $e->getMessage()),
		);
		self::assertInstanceOf(ReflectionException::class, $e->getPrevious());
	}

	public function provideUnSerializationFailure(): Generator
	{
		yield [
			static fn (PropertySource $source) => $source->getClass(),
		];

		yield [
			static fn (PropertySource $source) => $source->getReflector(),
		];

		yield [
			static fn (PropertySource $source) => $source->toString(),
		];

		yield [
			static fn (PropertySource $source) => serialize($source),
		];
	}

}
