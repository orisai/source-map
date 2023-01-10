<?php declare(strict_types = 1);

namespace Tests\Orisai\SourceMap\Unit;

use Generator;
use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\SourceMap\Exception\InvalidSource;
use Orisai\SourceMap\FileSource;
use PHPUnit\Framework\TestCase;
use function dirname;
use function serialize;
use function strlen;
use function unserialize;
use const PHP_EOL;

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

		$source = new FileSource(__FILE__, null, 1, 3);

		self::assertTrue($source->isValid());
		self::assertSame(1, $source->getLine());
		self::assertSame(3, $source->getColumn());
		self::assertSame("$fullPath:1:3", $source->toString());
		self::assertSame($source->toString(), (string) $source);
		self::assertEquals($source, unserialize(serialize($source)));
	}

	public function testLine(): void
	{
		$fullPath = __FILE__;

		$source = new FileSource(__FILE__, null, 1);

		self::assertTrue($source->isValid());
		self::assertSame(1, $source->getLine());
		self::assertNull($source->getColumn());
		self::assertSame("$fullPath:1", $source->toString());
		self::assertSame($source->toString(), (string) $source);
		self::assertEquals($source, unserialize(serialize($source)));
	}

	public function testColumn(): void
	{
		$fullPath = __FILE__;

		$source = new FileSource(__FILE__, null, null, 3);

		self::assertTrue($source->isValid());
		self::assertSame(1, $source->getLine());
		self::assertSame(3, $source->getColumn());
		self::assertSame("$fullPath:1:3", $source->toString());
		self::assertSame($source->toString(), (string) $source);
		self::assertEquals($source, unserialize(serialize($source)));
	}

	/**
	 * @dataProvider provideInvalid
	 */
	public function testInvalidFile(string $fullPath): void
	{
		$this->expectException(InvalidArgument::class);
		$this->expectExceptionMessage("File '$fullPath' does not exist.");

		new FileSource($fullPath);
	}

	public function provideInvalid(): Generator
	{
		yield [__DIR__ . '/non-existent.php'];
		yield [__DIR__];
	}

	public function testInvalidLine(): void
	{
		$fullPath = __DIR__ . '/../Doubles/file.txt';

		// Is okay
		new FileSource($fullPath, null, 5);

		$this->expectException(InvalidArgument::class);
		$this->expectExceptionMessage(
			"File '$fullPath' does not have 'line 6'.",
		);

		new FileSource($fullPath, null, 6);
	}

	public function testInvalidColumn(): void
	{
		$fullPath = __DIR__ . '/../Doubles/file.txt';

		// Is okay
		new FileSource($fullPath, null, 1, 1);
		new FileSource($fullPath, null, 1, 3);
		new FileSource($fullPath, null, 3, 1);

		$col = 1 + strlen(PHP_EOL);
		$this->expectException(InvalidArgument::class);
		$this->expectExceptionMessage(
			"File '$fullPath' at 'line 3' does not have 'column $col'.",
		);

		new FileSource($fullPath, null, 3, $col);
	}

	public function testInvalidUnSerialized(): void
	{
		$fullPath = __DIR__ . '/non-existent.php';
		$length = strlen($fullPath);
		// phpcs:ignore SlevomatCodingStandard.Files.LineLength
		$serialized = "O:27:\"Orisai\SourceMap\FileSource\":4:{s:8:\"fullPath\";s:$length:\"$fullPath\";s:8:\"basePath\";N;s:4:\"line\";N;s:6:\"column\";N;}";

		$source = unserialize($serialized);
		self::assertInstanceOf(FileSource::class, $source);

		self::assertFalse($source->isValid());
		self::assertSame($fullPath, $source->getFullPath());
		self::assertNull($source->getRelativePath());
		self::assertNull($source->getLine());
		self::assertNull($source->getColumn());
		self::assertSame($fullPath, $source->toString());
		self::assertEquals($source, unserialize(serialize($source)));

		$this->expectException(InvalidSource::class);
		$this->expectExceptionMessage("File '$fullPath' does not exist.");

		$source->getLastChange();
	}

}
