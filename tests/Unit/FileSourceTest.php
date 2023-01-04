<?php declare(strict_types = 1);

namespace Tests\Orisai\SourceMap\Unit;

use Generator;
use Orisai\SourceMap\FileSource;
use PHPUnit\Framework\TestCase;
use function dirname;
use function serialize;
use function strlen;
use function unserialize;

final class FileSourceTest extends TestCase
{

	public function test(): void
	{
		$fullPath = __FILE__;

		$source = new FileSource($fullPath);

		self::assertTrue($source->isValid());
		self::assertSame($fullPath, $source->getFullPath());
		self::assertNull($source->getRelativePath());
		self::assertSame($fullPath, $source->toString());
		self::assertEquals($source, unserialize(serialize($source)));
	}

	public function testSerializationBC(): void
	{
		$fullPath = __FILE__;
		$length = strlen($fullPath);
		$serialized = "O:27:\"Orisai\SourceMap\FileSource\":2:{s:8:\"fullPath\";s:$length:\"$fullPath\";s:8:\"basePath\";N;}";

		$source = unserialize($serialized);
		self::assertInstanceOf(FileSource::class, $source);

		self::assertTrue($source->isValid());
		self::assertSame($fullPath, $source->getFullPath());
		self::assertNull($source->getRelativePath());
		self::assertSame($fullPath, $source->toString());
		self::assertEquals($source, unserialize(serialize($source)));
	}

	public function testRelativePath(): void
	{
		$fullPath = __FILE__;
		$basePath = dirname($fullPath, 3);

		$source = new FileSource($fullPath, $basePath);

		self::assertTrue($source->isValid());
		self::assertSame($fullPath, $source->getFullPath());
		self::assertSame('tests/Unit/FileSourceTest.php', $source->getRelativePath());
		self::assertSame('.../tests/Unit/FileSourceTest.php', $source->toString());
		self::assertEquals($source, unserialize(serialize($source)));
	}

	/**
	 * @dataProvider provideInvalid
	 */
	public function testInvalid(string $fullPath): void
	{
		$source = new FileSource($fullPath);

		self::assertFalse($source->isValid());
	}

	public function provideInvalid(): Generator
	{
		yield [__DIR__ . '/non-existent.php'];
		yield [__DIR__];
	}

}
