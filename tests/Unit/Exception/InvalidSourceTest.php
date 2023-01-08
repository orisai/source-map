<?php declare(strict_types = 1);

namespace Tests\Orisai\SourceMap\Unit\Exception;

use Orisai\SourceMap\Exception\InvalidSource;
use Orisai\SourceMap\FileSource;
use PHPUnit\Framework\TestCase;

final class InvalidSourceTest extends TestCase
{

	public function test(): void
	{
		$source = new FileSource(__FILE__);

		$e = InvalidSource::create($source);
		self::assertSame($source, $e->getSource());
	}

}
