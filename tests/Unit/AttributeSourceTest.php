<?php declare(strict_types = 1);

namespace Tests\Orisai\SourceMap\Unit;

use Closure;
use Generator;
use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\Exceptions\Logic\InvalidState;
use Orisai\SourceMap\AnnotationSource;
use Orisai\SourceMap\AttributeSource;
use Orisai\SourceMap\ClassConstantSource;
use Orisai\SourceMap\ClassSource;
use Orisai\SourceMap\FunctionSource;
use Orisai\SourceMap\MethodSource;
use Orisai\SourceMap\ParameterSource;
use Orisai\SourceMap\PropertySource;
use Orisai\SourceMap\ReflectorSource;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;
use Tests\Orisai\SourceMap\Doubles\AnnotatedReflectedClass;
use Tests\Orisai\SourceMap\Doubles\EmptyClass;
use Tests\Orisai\SourceMap\Doubles\ReflectedClassWithAttributes;
use Tests\Orisai\SourceMap\Doubles\TestReflectorSource;
use function preg_replace;
use function serialize;
use function unserialize;
use const PHP_EOL;
use const PHP_VERSION_ID;

final class AttributeSourceTest extends TestCase
{

	/**
	 * @dataProvider provideTarget
	 */
	public function test(ReflectorSource $target, string $string): void
	{
		if (PHP_VERSION_ID < 8_00_00) {
			self::markTestSkipped('Attributes are supported on PHP 8.0+');
		}

		$source = new AttributeSource($target);

		self::assertTrue($source->isValid());
		self::assertSame($target, $source->getTarget());
		self::assertSame($target->getReflector(), $source->getReflector());
		self::assertSame("$string attribute", $source->toString());
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

	public function testWrappedTarget(): void
	{
		$target = new AnnotationSource(new ClassSource(new ReflectionClass(AnnotatedReflectedClass::class)));

		$this->expectException(InvalidArgument::class);
		$this->expectExceptionMessage(
			<<<'MSG'
Context: Creating 'Orisai\SourceMap\AttributeSource'.
Problem: Given class 'Orisai\SourceMap\AnnotationSource' implements
         'Orisai\SourceMap\AboveReflectorSource' and cannot be wrapped in
         another 'AboveReflectorSource'.
MSG,
		);

		new AttributeSource($target);
	}

	public function testReflectorWithNoAttributesSupport(): void
	{
		$target = new TestReflectorSource();

		$this->expectException(InvalidArgument::class);
		if (PHP_VERSION_ID < 8_00_00) {
			$this->expectExceptionMessage(
				<<<'MSG'
Context: Creating AttributeSource.
Problem: Targeted source does not have any attributes.
Hint: Attributes are supported since PHP 8.0
MSG,
			);
		} else {
			$this->expectExceptionMessage(
				<<<'MSG'
Context: Creating AttributeSource.
Problem: Targeted source does not have any attributes.
MSG,
			);
		}

		new AttributeSource($target);
	}

	public function testNoAttributes(): void
	{
		$target = new ClassSource(new ReflectionClass(EmptyClass::class));

		$this->expectException(InvalidArgument::class);
		if (PHP_VERSION_ID < 8_00_00) {
			$this->expectExceptionMessage(
				<<<'MSG'
Context: Creating AttributeSource.
Problem: Targeted source does not have any attributes.
Hint: Attributes are supported since PHP 8.0
MSG,
			);
		} else {
			$this->expectExceptionMessage(
				<<<'MSG'
Context: Creating AttributeSource.
Problem: Targeted source does not have any attributes.
MSG,
			);
		}

		new AttributeSource($target);
	}

	public function testNoAttributesUnSerialized(): void
	{
		// phpcs:ignore SlevomatCodingStandard.Files.LineLength
		$serialized = 'O:32:"Orisai\SourceMap\AttributeSource":1:{s:6:"target";O:28:"Orisai\SourceMap\ClassSource":1:{s:5:"class";s:41:"Tests\Orisai\SourceMap\Doubles\EmptyClass";}}';

		$source = unserialize($serialized);
		self::assertInstanceOf(AttributeSource::class, $source);
		self::assertFalse($source->isValid());

		$this->expectException(InvalidArgument::class);
		$this->expectExceptionMessage(
			<<<'MSG'
Context: Deserializing AttributeSource.
Problem: Targeted source does not have any attributes.
MSG,
		);

		$source->toString();
	}

	public function testSerializationBC(): void
	{
		if (PHP_VERSION_ID < 8_00_00) {
			self::markTestSkipped('Attributes are supported on PHP 8.0+');
		}

		// phpcs:ignore SlevomatCodingStandard.Files.LineLength
		$serialized = 'O:32:"Orisai\SourceMap\AttributeSource":1:{s:6:"target";O:28:"Orisai\SourceMap\ClassSource":1:{s:5:"class";s:59:"Tests\Orisai\SourceMap\Doubles\ReflectedClassWithAttributes";}}';

		$class = ReflectedClassWithAttributes::class;
		$target = new ClassSource(new ReflectionClass($class));

		$source = unserialize($serialized);
		self::assertInstanceOf(AttributeSource::class, $source);

		self::assertTrue($source->isValid());
		self::assertEquals($target, $source->getTarget());
		self::assertEquals($target->getReflector(), $source->getReflector());
		self::assertSame("$class attribute", $source->toString());
		self::assertEquals($source, unserialize(serialize($source)));
	}

	/**
	 * @dataProvider provideTargetUnSerializationFailure
	 */
	public function testTargetUnSerializationFailure(Closure $call): void
	{
		if (PHP_VERSION_ID < 8_00_00) {
			self::markTestSkipped('Attributes are supported on PHP 8.0+');
		}

		// phpcs:ignore SlevomatCodingStandard.Files.LineLength
		$serialized = 'O:32:"Orisai\SourceMap\AttributeSource":1:{s:6:"target";O:28:"Orisai\SourceMap\ClassSource":1:{s:5:"class";s:42:"Tests\Orisai\SourceMap\Doubles\NonExistent";}}';
		$source = unserialize($serialized);
		self::assertInstanceOf(AttributeSource::class, $source);

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
		self::assertSame(
			<<<'MSG'
Deserialization failed due to following error:
Class "Tests\Orisai\SourceMap\Doubles\NonExistent" does not exist
MSG,
			preg_replace('~\R~u', PHP_EOL, $e->getMessage()),
		);

		self::assertInstanceOf(ReflectionException::class, $e->getPrevious());
	}

	public function provideTargetUnSerializationFailure(): Generator
	{
		yield [
			static fn (AttributeSource $source) => $source->getReflector(),
		];

		yield [
			static fn (AttributeSource $source) => $source->toString(),
		];

		yield [
			static fn (AttributeSource $source) => serialize($source),
		];
	}

}
