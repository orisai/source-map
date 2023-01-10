<?php declare(strict_types = 1);

namespace Tests\Orisai\SourceMap\Doubles;

use DateTimeImmutable;
use Orisai\Exceptions\Logic\ShouldNotHappen;
use Orisai\SourceMap\Check\SourceCheckHandler;
use Orisai\SourceMap\Source;

final class TestEmptySourceCheckHandler implements SourceCheckHandler
{

	public static function getSupported(): array
	{
		return [];
	}

	public function isValid(Source $source): bool
	{
		throw ShouldNotHappen::create()->withMessage('unsupported');
	}

	public function getLastChange(Source $source): DateTimeImmutable
	{
		throw ShouldNotHappen::create()->withMessage('unsupported');
	}

}
