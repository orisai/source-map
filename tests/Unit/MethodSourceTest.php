<?php declare(strict_types = 1);

namespace Tests\Orisai\SourceMap\Unit;

use Orisai\SourceMap\ClassSource;
use Orisai\SourceMap\MethodSource;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use Tests\Orisai\SourceMap\Doubles\AnnotatedReflectedClass;
use function serialize;
use function unserialize;

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
		self::assertSame("{$class}->test(foo)", $source->toString(['foo']));
		self::assertSame("{$class}->test(foo, bar)", $source->toString(['foo', 'bar']));

		self::assertEquals($source, unserialize(serialize($source)));
	}

	public function testStatic(): void
	{
		$class = AnnotatedReflectedClass::class;
		$reflector = new ReflectionMethod($class, 'staticTest');

		$source = new MethodSource($reflector);

		self::assertSame("$class::staticTest()", $source->toString());
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

}
