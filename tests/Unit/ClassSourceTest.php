<?php declare(strict_types = 1);

namespace Tests\Orisai\SourceMap\Unit;

use Closure;
use Generator;
use Orisai\Exceptions\Logic\InvalidState;
use Orisai\SourceMap\ClassSource;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;
use Tests\Orisai\SourceMap\Doubles\AnnotatedReflectedClass;
use function preg_replace;
use function serialize;
use function unserialize;
use const PHP_EOL;
use const PHP_VERSION_ID;

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

	/**
	 * @dataProvider provideUnSerializationFailure
	 */
	public function testUnSerializationFailure(Closure $call): void
	{
		// phpcs:ignore SlevomatCodingStandard.Files.LineLength
		$serialized = 'O:28:"Orisai\SourceMap\ClassSource":1:{s:5:"class";s:42:"Tests\Orisai\SourceMap\Doubles\NonExistent";}';
		$source = unserialize($serialized);
		self::assertInstanceOf(ClassSource::class, $source);

		self::assertFalse($source->isValid());

		$e = null;
		try {
			$call($source);
		} catch (InvalidState $e) {
			//  Handled bellow
		}

		self::assertInstanceOf(InvalidState::class, $e);
		if (PHP_VERSION_ID < 8_00_00) {
			self::assertSame(
				<<<'MSG'
Deserialization failed due to following error:
Class Tests\Orisai\SourceMap\Doubles\NonExistent does not exist
MSG,
				preg_replace('~\R~u', PHP_EOL, $e->getMessage()),
			);

		} else {
			self::assertSame(
				<<<'MSG'
Deserialization failed due to following error:
Class "Tests\Orisai\SourceMap\Doubles\NonExistent" does not exist
MSG,
				preg_replace('~\R~u', PHP_EOL, $e->getMessage()),
			);
		}

		self::assertInstanceOf(ReflectionException::class, $e->getPrevious());
	}

	public function provideUnSerializationFailure(): Generator
	{
		yield [
			static fn (ClassSource $source) => $source->getReflector(),
		];

		yield [
			static fn (ClassSource $source) => $source->toString(),
		];

		yield [
			static fn (ClassSource $source) => serialize($source),
		];
	}

}
