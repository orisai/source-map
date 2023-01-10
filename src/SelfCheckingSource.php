<?php declare(strict_types = 1);

namespace Orisai\SourceMap;

use DateTimeImmutable;

interface SelfCheckingSource extends Source
{

	public function isValid(): bool;

	public function getLastChange(): DateTimeImmutable;

}
