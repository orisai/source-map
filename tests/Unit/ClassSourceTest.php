<?php declare(strict_types = 1);

namespace Tests\Orisai\SourceMap\Unit;

use Orisai\SourceMap\ClassSource;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Tests\Orisai\SourceMap\Doubles\AnnotatedReflectedClass;
use function serialize;
use function unserialize;

final class ClassSourceTest extends TestCase
{

	public function test(): void
	{
		$class = AnnotatedReflectedClass::class;
		$reflector = new ReflectionClass($class);

		$source = new ClassSource($reflector);

		self::assertTrue($source->isValid());
		self::assertSame($reflector, $source->getReflector());
		self::assertSame($class, $source->toString());
		self::assertEquals($source, unserialize(serialize($source)));
	}

	public function testSerializationBC(): void
	{
		// phpcs:ignore SlevomatCodingStandard.Files.LineLength
		$serialized = 'O:28:"Orisai\SourceMap\ClassSource":1:{s:5:"class";s:54:"Tests\Orisai\SourceMap\Doubles\AnnotatedReflectedClass";}';

		$class = AnnotatedReflectedClass::class;
		$reflector = new ReflectionClass($class);

		$source = unserialize($serialized);
		self::assertInstanceOf(ClassSource::class, $source);

		self::assertTrue($source->isValid());
		self::assertEquals($reflector, $source->getReflector());
		self::assertSame($class, $source->toString());
		self::assertEquals($source, unserialize(serialize($source)));
	}

}
