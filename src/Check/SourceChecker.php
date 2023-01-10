<?php declare(strict_types = 1);

namespace Orisai\SourceMap\Check;

use DateTimeImmutable;
use Orisai\SourceMap\Source;

interface SourceChecker
{

	public function isValid(Source $source): bool;

	public function getLastChange(Source $source): DateTimeImmutable;

}
