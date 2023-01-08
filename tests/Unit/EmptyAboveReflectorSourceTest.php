<?php declare(strict_types = 1);

namespace Tests\Orisai\SourceMap\Unit;

use Generator;
use Orisai\SourceMap\AnnotationSource;
use Orisai\SourceMap\ClassConstantSource;
use Orisai\SourceMap\ClassSource;
use Orisai\SourceMap\EmptyAboveReflectorSource;
use Orisai\SourceMap\Exception\InvalidSource;
use Orisai\SourceMap\FunctionSource;
use Orisai\SourceMap\MethodSource;
use Orisai\SourceMap\ParameterSource;
use Orisai\SourceMap\PropertySource;
use Orisai\SourceMap\ReflectorSource;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;
use Tests\Orisai\SourceMap\Doubles\AnnotatedReflectedClass;
use function serialize;
use function unserialize;

final class EmptyAboveReflectorSourceTest extends TestCase
{

	/**
	 * @dataProvider provideTarget
	 */
	public function test(ReflectorSource $target, string $string): void
	{
		$source = new EmptyAboveReflectorSource($target);

		self::assertTrue($source->isValid());
		self::assertSame($target, $source->getTarget());
		self::assertSame($target->getReflector(), $source->getReflector());

		self::assertSame("$string empty source", $source->toString());
		self::assertSame($source->toString(), (string) $source);

		self::assertEquals($target->getLastChange(), $source->getLastChange());
		self::assertGreaterThanOrEqual(2_023, (int) $source->getLastChange()->format('Y'));

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

		yield [
			new ParameterSource(new ReflectionParameter([$class, 'test'], 'test')),
			"{$class}->test(test)",
		];
	}

	public function testWrappedTarget(): void
	{
		$target = new AnnotationSource(new ClassSource(new ReflectionClass(AnnotatedReflectedClass::class)));

		$this->expectException(InvalidSource::class);
		$this->expectExceptionMessage(
			<<<'MSG'
Context: Creating 'Orisai\SourceMap\EmptyAboveReflectorSource'.
Problem: Given class 'Orisai\SourceMap\AnnotationSource' implements
         'Orisai\SourceMap\AboveReflectorSource' and cannot be wrapped in
         another 'AboveReflectorSource'.
MSG,
		);

		new EmptyAboveReflectorSource($target);
	}

	public function testSerializationBC(): void
	{
		// phpcs:ignore SlevomatCodingStandard.Files.LineLength
		$serialized = 'O:42:"Orisai\SourceMap\EmptyAboveReflectorSource":1:{s:6:"target";O:28:"Orisai\SourceMap\ClassSource":1:{s:5:"class";s:54:"Tests\Orisai\SourceMap\Doubles\AnnotatedReflectedClass";}}';

		$class = AnnotatedReflectedClass::class;
		$target = new ClassSource(new ReflectionClass($class));

		$source = unserialize($serialized);
		self::assertInstanceOf(EmptyAboveReflectorSource::class, $source);

		self::assertTrue($source->isValid());
		self::assertEquals($target, $source->getTarget());
		self::assertEquals($target->getReflector(), $source->getReflector());
		self::assertSame("$class empty source", $source->toString());
		self::assertEquals($source, unserialize(serialize($source)));
	}

}
