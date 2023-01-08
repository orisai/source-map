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
		self::assertNull($source->getLine());
		self::assertNull($source->getColumn());

		self::assertSame($fullPath, $source->toString());
		self::assertSame($source->toString(), (string) $source);

		self::assertGreaterThanOrEqual(2_023, (int) $source->getLastChange()->format('Y'));

		self::assertEquals($source, unserialize(serialize($source)));
	}

	public function testSerializationBC(): void
	{
		$fullPath = __FILE__;
		$length = strlen($fullPath);
		// phpcs:ignore SlevomatCodingStandard.Files.LineLength
		$serialized = "O:27:\"Orisai\SourceMap\FileSource\":4:{s:8:\"fullPath\";s:$length:\"$fullPath\";s:8:\"basePath\";N;s:4:\"line\";N;s:6:\"column\";N;}";

		$source = unserialize($serialized);
		self::assertInstanceOf(FileSource::class, $source);

		self::assertTrue($source->isValid());
		self::assertSame($fullPath, $source->getFullPath());
		self::assertNull($source->getRelativePath());
		self::assertNull($source->getLine());
		self::assertNull($source->getColumn());
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
		self::assertSame($source->toString(), (string) $source);
		self::assertEquals($source, unserialize(serialize($source)));
	}

	public function testLineColumn(): void
	{
		$fullPath = __FILE__;

		$source = new FileSource(__FILE__, null, 69, 666);

		self::assertTrue($source->isValid());
		self::assertSame(69, $source->getLine());
		self::assertSame(666, $source->getColumn());
		self::assertSame("$fullPath:69:666", $source->toString());
		self::assertSame($source->toString(), (string) $source);
		self::assertEquals($source, unserialize(serialize($source)));
	}

	public function testLine(): void
	{
		$fullPath = __FILE__;

		$source = new FileSource(__FILE__, null, 42);

		self::assertTrue($source->isValid());
		self::assertSame(42, $source->getLine());
		self::assertNull($source->getColumn());
		self::assertSame("$fullPath:42", $source->toString());
		self::assertSame($source->toString(), (string) $source);
		self::assertEquals($source, unserialize(serialize($source)));
	}

	public function testColumn(): void
	{
		$fullPath = __FILE__;

		$source = new FileSource(__FILE__, null, null, 420);

		self::assertTrue($source->isValid());
		self::assertSame(1, $source->getLine());
		self::assertSame(420, $source->getColumn());
		self::assertSame("$fullPath:1:420", $source->toString());
		self::assertSame($source->toString(), (string) $source);
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
