<?php declare(strict_types = 1);

namespace Tests\Orisai\SourceMap\Unit;

use Closure;
use Generator;
use Orisai\Exceptions\Logic\InvalidState;
use Orisai\SourceMap\ClassConstantSource;
use Orisai\SourceMap\ClassSource;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionException;
use Tests\Orisai\SourceMap\Doubles\AnnotatedReflectedClass;
use function preg_replace;
use function serialize;
use function unserialize;
use const PHP_EOL;
use const PHP_VERSION_ID;

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

	/**
	 * @dataProvider provideUnSerializationFailure
	 */
	public function testUnSerializationFailure(Closure $call): void
	{
		// phpcs:ignore SlevomatCodingStandard.Files.LineLength
		$serialized = 'O:36:"Orisai\SourceMap\ClassConstantSource":2:{s:5:"class";s:41:"Tests\Orisai\SourceMap\Doubles\EmptyClass";s:8:"constant";s:4:"Test";}';
		$source = unserialize($serialized);
		self::assertInstanceOf(ClassConstantSource::class, $source);

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
Class Constant Tests\Orisai\SourceMap\Doubles\EmptyClass::Test does not exist
MSG,
				preg_replace('~\R~u', PHP_EOL, $e->getMessage()),
			);
		} else {
			self::assertSame(
				<<<'MSG'
Deserialization failed due to following error:
Constant Tests\Orisai\SourceMap\Doubles\EmptyClass::Test does not exist
MSG,
				preg_replace('~\R~u', PHP_EOL, $e->getMessage()),
			);
		}

		self::assertInstanceOf(ReflectionException::class, $e->getPrevious());
	}

	public function provideUnSerializationFailure(): Generator
	{
		yield [
			static fn (ClassConstantSource $source) => $source->getClass(),
		];

		yield [
			static fn (ClassConstantSource $source) => $source->getReflector(),
		];

		yield [
			static fn (ClassConstantSource $source) => $source->toString(),
		];

		yield [
			static fn (ClassConstantSource $source) => serialize($source),
		];
	}

}
