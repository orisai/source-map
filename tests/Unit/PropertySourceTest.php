<?php declare(strict_types = 1);

namespace Tests\Orisai\SourceMap\Unit;

use Orisai\SourceMap\ClassSource;
use Orisai\SourceMap\PropertySource;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionProperty;
use Tests\Orisai\SourceMap\Doubles\AnnotatedReflectedClass;
use function serialize;
use function unserialize;

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

}
