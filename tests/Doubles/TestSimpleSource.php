<?php declare(strict_types = 1);

namespace Tests\Orisai\SourceMap\Doubles;

use Orisai\SourceMap\Source;

final class TestSimpleSource implements Source
{

	public function toString(): string
	{
		return 'simple';
	}

	public function __toString(): string
	{
		return $this->toString();
	}

	public function __serialize(): array
	{
		return [];
	}

	public function __unserialize(array $data): void
	{
		// noop
	}

}
