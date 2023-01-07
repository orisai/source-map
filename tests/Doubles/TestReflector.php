<?php declare(strict_types = 1);

namespace Tests\Orisai\SourceMap\Doubles;

use Reflector;

final class TestReflector implements Reflector
{

	public static function export(): ?string
	{
		return null;
	}

	public function __toString(): string
	{
		return '';
	}

}
