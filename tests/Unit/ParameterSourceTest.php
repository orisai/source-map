<?php declare(strict_types = 1);

namespace Tests\Orisai\SourceMap\Unit;

use Orisai\SourceMap\FunctionSource;
use Orisai\SourceMap\MethodSource;
use Orisai\SourceMap\ParameterSource;
use PHPUnit\Framework\TestCase;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionParameter;
use Tests\Orisai\SourceMap\Doubles\AnnotatedReflectedClass;
use function serialize;
use function unserialize;

final class ParameterSourceTest extends TestCase
{

	public static function setUpBeforeClass(): void
	{
		parent::setUpBeforeClass();

		require_once __DIR__ . '/../Doubles/testFunction.php';
	}

	public function test(): void
	{
		$class = AnnotatedReflectedClass::class;
		$reflector = new ReflectionParameter([$class, 'test'], 'test');
		$methodSource = new MethodSource(new ReflectionMethod($class, 'test'));

		$source = new ParameterSource($reflector);

		self::assertTrue($source->isValid());
		self::assertEquals($methodSource, $source->getFunction());
		self::assertSame($reflector, $source->getReflector());
		self::assertSame("{$class}->test(test)", $source->toString());
		self::assertEquals($source, unserialize(serialize($source)));
	}

	public function testFunction(): void
	{
		$function = 'Tests\Orisai\SourceMap\Doubles\test';
		$reflector = new ReflectionParameter($function, 'test');
		$functionSource = new FunctionSource(new ReflectionFunction($function));

		$source = new ParameterSource($reflector);

		self::assertTrue($source->isValid());
		self::assertEquals($functionSource, $source->getFunction());
		self::assertSame($reflector, $source->getReflector());
		self::assertSame("$function(test)", $source->toString());
		self::assertEquals($source, unserialize(serialize($source)));
	}

	public function testSerializationBC(): void
	{
		// phpcs:ignore SlevomatCodingStandard.Files.LineLength
		$serialized = 'O:32:"Orisai\SourceMap\ParameterSource":3:{s:5:"class";s:54:"Tests\Orisai\SourceMap\Doubles\AnnotatedReflectedClass";s:8:"function";s:4:"test";s:9:"parameter";s:4:"test";}';

		$class = AnnotatedReflectedClass::class;
		$reflector = new ReflectionParameter([$class, 'test'], 'test');
		$methodSource = new MethodSource(new ReflectionMethod($class, 'test'));

		$source = unserialize($serialized);
		self::assertInstanceOf(ParameterSource::class, $source);

		self::assertTrue($source->isValid());
		self::assertEquals($methodSource, $source->getFunction());
		self::assertEquals($reflector, $source->getReflector());
		self::assertSame("{$class}->test(test)", $source->toString());
		self::assertEquals($source, unserialize(serialize($source)));
	}

}
