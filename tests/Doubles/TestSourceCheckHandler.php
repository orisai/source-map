<?php declare(strict_types = 1);

namespace Tests\Orisai\SourceMap\Doubles;

use DateTimeImmutable;
use Orisai\Exceptions\Logic\ShouldNotHappen;
use Orisai\SourceMap\Check\SourceCheckHandler;
use Orisai\SourceMap\SelfCheckingSource;
use Orisai\SourceMap\Source;

final class TestSourceCheckHandler implements SourceCheckHandler
{

	private bool $valid;

	private DateTimeImmutable $lastChange;

	public function __construct(bool $valid, DateTimeImmutable $lastChange)
	{
		$this->valid = $valid;
		$this->lastChange = $lastChange;
	}

	public static function getSupported(): array
	{
		return [
			SelfCheckingSource::class,
			TestSimpleSource::class,
		];
	}

	public function isValid(Source $source): bool
	{
		if ($source instanceof SelfCheckingSource) {
			throw ShouldNotHappen::create()->withMessage('self checking');
		}

		if ($source instanceof TestSimpleSource) {
			return $this->valid;
		}

		throw ShouldNotHappen::create()->withMessage('unsupported');
	}

	public function getLastChange(Source $source): DateTimeImmutable
	{
		if ($source instanceof SelfCheckingSource) {
			throw ShouldNotHappen::create()->withMessage('self checking');
		}

		if ($source instanceof TestSimpleSource) {
			return $this->lastChange;
		}

		throw ShouldNotHappen::create()->withMessage('unsupported');
	}

}
