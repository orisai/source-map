<?php declare(strict_types = 1);

namespace Tests\Orisai\SourceMap\Unit;

use Orisai\SourceMap\ClassConstantSource;
use Orisai\SourceMap\ClassSource;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionClassConstant;
use Tests\Orisai\SourceMap\Doubles\AnnotatedReflectedClass;
use function serialize;
use function unserialize;

final class ClassConstantSourceTest extends TestCase
{

	public function test(): void
	{
		$class = AnnotatedReflectedClass::class;
		$reflector = new ReflectionClassConstant($class, 'Test');
		$classSource = new ClassSource(new ReflectionClass($class));

		$source = new ClassConstantSource($reflector);

		self::assertTrue($source->isValid());
		self::assertEquals($classSource, $source->getClass());
		self::assertSame($reflector, $source->getReflector());
		self::assertSame("$class::Test", $source->toString());
		self::assertEquals($source, unserialize(serialize($source)));
	}

	public function testSerializationBC(): void
	{
		// phpcs:ignore SlevomatCodingStandard.Files.LineLength
		$serialized = 'O:36:"Orisai\SourceMap\ClassConstantSource":2:{s:5:"class";s:54:"Tests\Orisai\SourceMap\Doubles\AnnotatedReflectedClass";s:8:"constant";s:4:"Test";}';

		$class = AnnotatedReflectedClass::class;
		$reflector = new ReflectionClassConstant($class, 'Test');
		$classSource = new ClassSource(new ReflectionClass($class));

		$source = unserialize($serialized);
		self::assertInstanceOf(ClassConstantSource::class, $source);

		self::assertTrue($source->isValid());
		self::assertEquals($classSource, $source->getClass());
		self::assertEquals($reflector, $source->getReflector());
		self::assertSame("$class::Test", $source->toString());
		self::assertEquals($source, unserialize(serialize($source)));
	}

}
