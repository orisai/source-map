<?php declare(strict_types = 1);

namespace Tests\Orisai\SourceMap\Unit\Check;

use DateTimeImmutable;
use Generator;
use Orisai\Exceptions\Logic\InvalidState;
use Orisai\SourceMap\Check\DefaultSourceChecker;
use PHPUnit\Framework\TestCase;
use Tests\Orisai\SourceMap\Doubles\TestEmptySourceCheckHandler;
use Tests\Orisai\SourceMap\Doubles\TestSelfCheckingSource;
use Tests\Orisai\SourceMap\Doubles\TestSimpleSource;
use Tests\Orisai\SourceMap\Doubles\TestSourceCheckHandler;

final class DefaultSourceCheckerTest extends TestCase
{

	/**
	 * @dataProvider provideValues
	 */
	public function testSelfChecking(bool $valid, DateTimeImmutable $lastChange): void
	{
		$checker = new DefaultSourceChecker();
		$source = new TestSelfCheckingSource($valid, $lastChange);

		self::assertSame($source->isValid(), $checker->isValid($source));
		self::assertSame($source->getLastChange(), $checker->getLastChange($source));
	}

	/**
	 * @dataProvider provideValues
	 */
	public function testHandler(bool $valid, DateTimeImmutable $lastChange): void
	{
		$checker = new DefaultSourceChecker();
		$checker->addHandler(new TestEmptySourceCheckHandler());
		$checker->addHandler(new TestSourceCheckHandler($valid, $lastChange));
		$source = new TestSimpleSource();

		self::assertSame($valid, $checker->isValid($source));
		self::assertSame($lastChange, $checker->getLastChange($source));
	}

	public function provideValues(): Generator
	{
		yield [true, new DateTimeImmutable()];
		yield [false, DateTimeImmutable::createFromFormat('U', '0')];
	}

	public function testNoHandlerIsValid(): void
	{
		$checker = new DefaultSourceChecker();

		$this->expectException(InvalidState::class);
		$this->expectExceptionMessage(
			<<<'MSG'
Context: Checking whether 'Tests\Orisai\SourceMap\Doubles\TestSimpleSource' is a
         valid source.
Problem: No handler handles this source.
Solution: Add handler for the source or make the source implement
          'Orisai\SourceMap\SelfCheckingSource'.
MSG,
		);

		$checker->isValid(new TestSimpleSource());
	}

	public function testNoHandlerGetLastChange(): void
	{
		$checker = new DefaultSourceChecker();

		$this->expectException(InvalidState::class);
		$this->expectExceptionMessage(
			<<<'MSG'
Context: Getting last change of a source
         'Tests\Orisai\SourceMap\Doubles\TestSimpleSource'.
Problem: No handler handles this source.
Solution: Add handler for the source or make the source implement
          'Orisai\SourceMap\SelfCheckingSource'.
MSG,
		);

		$checker->getLastChange(new TestSimpleSource());
	}

}
