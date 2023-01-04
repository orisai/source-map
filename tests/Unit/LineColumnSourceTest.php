<?php declare(strict_types = 1);

namespace Tests\Orisai\SourceMap\Unit;

use Orisai\SourceMap\FileSource;
use Orisai\SourceMap\LineColumnSource;
use PHPUnit\Framework\TestCase;
use function serialize;
use function strlen;
use function unserialize;

final class LineColumnSourceTest extends TestCase
{

	public function test(): void
	{
		$fullPath = __FILE__;
		$fileSource = new FileSource($fullPath);

		$source = new LineColumnSource($fileSource, 1, 5);

		self::assertTrue($source->isValid());
		self::assertSame($fileSource, $source->getFile());
		self::assertSame(1, $source->getLine());
		self::assertSame(5, $source->getColumn());
		self::assertSame("$fullPath:1:5", $source->toString());
		self::assertEquals($source, unserialize(serialize($source)));
	}

	public function testNoColumn(): void
	{
		$fullPath = __FILE__;
		$fileSource = new FileSource($fullPath);

		$source = new LineColumnSource($fileSource, 1);

		self::assertTrue($source->isValid());
		self::assertSame($fileSource, $source->getFile());
		self::assertSame(1, $source->getLine());
		self::assertNull($source->getColumn());
		self::assertSame("$fullPath:1", $source->toString());
		self::assertEquals($source, unserialize(serialize($source)));
	}

	public function testSerializationBC(): void
	{
		$fullPath = __FILE__;
		$length = strlen($fullPath);

		// phpcs:ignore SlevomatCodingStandard.Files.LineLength
		$serialized = "O:33:\"Orisai\SourceMap\LineColumnSource\":3:{s:4:\"file\";O:27:\"Orisai\SourceMap\FileSource\":2:{s:8:\"fullPath\";s:$length:\"$fullPath\";s:8:\"basePath\";N;}s:4:\"line\";i:1;s:6:\"column\";i:5;}";

		$fileSource = new FileSource($fullPath);

		$source = unserialize($serialized);
		self::assertInstanceOf(LineColumnSource::class, $source);

		self::assertTrue($source->isValid());
		self::assertEquals($fileSource, $source->getFile());
		self::assertSame(1, $source->getLine());
		self::assertSame(5, $source->getColumn());
		self::assertSame("$fullPath:1:5", $source->toString());
		self::assertEquals($source, unserialize(serialize($source)));
	}

}
