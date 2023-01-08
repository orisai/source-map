<?php declare(strict_types = 1);

namespace Tests\Orisai\SourceMap\Unit;

use Closure;
use Generator;
use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\Exceptions\Logic\InvalidState;
use Orisai\SourceMap\AnnotationSource;
use Orisai\SourceMap\ClassConstantSource;
use Orisai\SourceMap\ClassSource;
use Orisai\SourceMap\EmptyAboveReflectorSource;
use Orisai\SourceMap\FunctionSource;
use Orisai\SourceMap\MethodSource;
use Orisai\SourceMap\PropertySource;
use Orisai\SourceMap\ReflectorSource;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionProperty;
use Tests\Orisai\SourceMap\Doubles\AnnotatedReflectedClass;
use Tests\Orisai\SourceMap\Doubles\EmptyClass;
use Tests\Orisai\SourceMap\Doubles\TestReflectorSource;
use function preg_replace;
use function serialize;
use function unserialize;
use const PHP_EOL;
use const PHP_VERSION_ID;

final class AnnotationSourceTest extends TestCase
{

	/**
	 * @dataProvider provideTarget
	 */
	public function test(ReflectorSource $target, string $string): void
	{
		$source = new AnnotationSource($target);

		self::assertTrue($source->isValid());
		self::assertSame($target, $source->getTarget());
		self::assertSame($target->getReflector(), $source->getReflector());
		self::assertSame("$string annotation", $source->toString());
		self::assertSame($source->toString(), (string) $source);
		self::assertEquals($source, unserialize(serialize($source)));
	}

	public function provideTarget(): Generator
	{
		$class = AnnotatedReflectedClass::class;

		yield [
			new ClassSource(new ReflectionClass($class)),
			$class,
		];

		yield [
			new ClassConstantSource(new ReflectionClassConstant($class, 'Test')),
			"$class::Test",
		];

		yield [
			new PropertySource(new ReflectionProperty($class, 'test')),
			"{$class}->\$test",
		];

		require_once __DIR__ . '/../Doubles/testFunction.php';

		yield [
			new FunctionSource(new ReflectionFunction('Tests\Orisai\SourceMap\Doubles\test')),
			'Tests\Orisai\SourceMap\Doubles\test()',
		];

		yield [
			new MethodSource(new ReflectionMethod($class, 'test')),
			"{$class}->test()",
		];
	}

	public function testWrappedTarget(): void
	{
		$target = new EmptyAboveReflectorSource(new ClassSource(new ReflectionClass(EmptyClass::class)));

		$this->expectException(InvalidArgument::class);
		$this->expectExceptionMessage(
			<<<'MSG'
Context: Creating 'Orisai\SourceMap\AnnotationSource'.
Problem: Given class 'Orisai\SourceMap\EmptyAboveReflectorSource' implements
         'Orisai\SourceMap\AboveReflectorSource' and cannot be wrapped in
         another 'AboveReflectorSource'.
MSG,
		);

		new AnnotationSource($target);
	}

	public function testReflectorWithNoCommentsSupport(): void
	{
		$target = new TestReflectorSource();

		$this->expectException(InvalidArgument::class);
		$this->expectExceptionMessage(
			<<<'MSG'
Context: Creating AnnotationSource.
Problem: Targeted source does not have any annotations.
MSG,
		);

		new AnnotationSource($target);
	}

	public function testNoComments(): void
	{
		$target = new ClassSource(new ReflectionClass(EmptyClass::class));

		$this->expectException(InvalidArgument::class);
		$this->expectExceptionMessage(
			<<<'MSG'
Context: Creating AnnotationSource.
Problem: Targeted source does not have any annotations.
MSG,
		);

		new AnnotationSource($target);
	}

	public function testNoCommentsUnSerialized(): void
	{
		// phpcs:ignore SlevomatCodingStandard.Files.LineLength
		$serialized = 'O:33:"Orisai\SourceMap\AnnotationSource":1:{s:6:"target";O:28:"Orisai\SourceMap\ClassSource":1:{s:5:"class";s:41:"Tests\Orisai\SourceMap\Doubles\EmptyClass";}}';

		$source = unserialize($serialized);
		self::assertInstanceOf(AnnotationSource::class, $source);
		self::assertFalse($source->isValid());

		$this->expectException(InvalidArgument::class);
		$this->expectExceptionMessage(
			<<<'MSG'
Context: Deserializing AnnotationSource.
Problem: Targeted source does not have any annotations.
MSG,
		);

		$source->toString();
	}

	public function testSerializationBC(): void
	{
		// phpcs:ignore SlevomatCodingStandard.Files.LineLength
		$serialized = 'O:33:"Orisai\SourceMap\AnnotationSource":1:{s:6:"target";O:28:"Orisai\SourceMap\ClassSource":1:{s:5:"class";s:54:"Tests\Orisai\SourceMap\Doubles\AnnotatedReflectedClass";}}';

		$class = AnnotatedReflectedClass::class;
		$target = new ClassSource(new ReflectionClass($class));

		$source = unserialize($serialized);
		self::assertInstanceOf(AnnotationSource::class, $source);

		self::assertTrue($source->isValid());
		self::assertEquals($target, $source->getTarget());
		self::assertEquals($target->getReflector(), $source->getReflector());
		self::assertSame("$class annotation", $source->toString());
		self::assertEquals($source, unserialize(serialize($source)));
	}

	/**
	 * @dataProvider provideTargetUnSerializationFailure
	 */
	public function testTargetUnSerializationFailure(Closure $call): void
	{
		// phpcs:ignore SlevomatCodingStandard.Files.LineLength
		$serialized = 'O:33:"Orisai\SourceMap\AnnotationSource":1:{s:6:"target";O:28:"Orisai\SourceMap\ClassSource":1:{s:5:"class";s:42:"Tests\Orisai\SourceMap\Doubles\NonExistent";}}';
		$source = unserialize($serialized);
		self::assertInstanceOf(AnnotationSource::class, $source);

		self::assertFalse($source->isValid());
		self::assertInstanceOf(ClassSource::class, $source->getTarget());
		self::assertFalse($source->getTarget()->isValid());

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

	public function provideTargetUnSerializationFailure(): Generator
	{
		yield [
			static fn (AnnotationSource $source) => $source->getReflector(),
		];

		yield [
			static fn (AnnotationSource $source) => $source->toString(),
		];

		yield [
			static fn (AnnotationSource $source) => serialize($source),
		];
	}

}
