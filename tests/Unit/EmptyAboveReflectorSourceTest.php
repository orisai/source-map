<?php declare(strict_types = 1);

namespace Tests\Orisai\SourceMap\Unit;

use Generator;
use Orisai\SourceMap\ClassConstantSource;
use Orisai\SourceMap\ClassSource;
use Orisai\SourceMap\EmptyAboveReflectorSource;
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
use Tests\Orisai\SourceMap\Doubles\ReflectedClassWithAttributes;
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
		self::assertEquals($source, unserialize(serialize($source)));
	}

	public function provideTarget(): Generator
	{
		$class = ReflectedClassWithAttributes::class;

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

	public function testSerializationBC(): void
	{
		// phpcs:ignore SlevomatCodingStandard.Files.LineLength
		$serialized = 'O:42:"Orisai\SourceMap\EmptyAboveReflectorSource":1:{s:6:"target";O:28:"Orisai\SourceMap\ClassSource":1:{s:5:"class";s:59:"Tests\Orisai\SourceMap\Doubles\ReflectedClassWithAttributes";}}';

		$class = ReflectedClassWithAttributes::class;
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
