<?php declare(strict_types = 1);

namespace Orisai\SourceMap;

use DateTimeImmutable;
use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\SourceMap\Exception\InvalidSource;
use SplFileObject;
use Symfony\Component\Filesystem\Path;
use function assert;
use function filemtime;
use function is_file;

/**
 * @readonly
 */
final class FileSource implements Source
{

	private string $fullPath;

	private ?string $basePath;

	/** @var int<1, max>|null */
	private ?int $line;

	/** @var int<1, max>|null */
	private ?int $column;

	/**
	 * @param int<1, max>|null $line
	 * @param int<1, max>|null $column
	 */
	public function __construct(string $fullPath, ?string $basePath = null, ?int $line = null, ?int $column = null)
	{
		if ($column !== null && $line === null) {
			$line = 1;
		}

		$this->fullPath = $fullPath;
		$this->basePath = $basePath;
		$this->line = $line;
		$this->column = $column;

		$this->throwIfInvalid(false);
	}

	public function getFullPath(): string
	{
		return $this->fullPath;
	}

	public function getRelativePath(): ?string
	{
		if ($this->basePath === null) {
			return null;
		}

		return Path::makeRelative($this->fullPath, $this->basePath);
	}

	/**
	 * @return int<1, max>|null
	 */
	public function getLine(): ?int
	{
		return $this->line;
	}

	/**
	 * @return int<1, max>|null
	 */
	public function getColumn(): ?int
	{
		return $this->column;
	}

	public function toString(): string
	{
		$relative = $this->getRelativePath();

		$path = $relative !== null
			? ".../$relative"
			: $this->getFullPath();

		if ($this->line !== null) {
			$path .= ":$this->line";
		}

		if ($this->column !== null) {
			$path .= ":$this->column";
		}

		return $path;
	}

	public function isValid(): bool
	{
		try {
			$this->throwIfInvalid(true);
		} catch (InvalidSource $exception) {
			return false;
		}

		return true;
	}

	private function throwIfInvalid(bool $existingSource): void
	{
		$e = $existingSource ? InvalidSource::create($this) : InvalidArgument::create();

		if (!is_file($this->fullPath)) {
			throw $e
				->withMessage("File '$this->fullPath' does not exist.");
		}

		if ($this->line !== null) {
			$file = new SplFileObject($this->fullPath);
			$file->seek($this->line - 1);

			$lineContent = $file->current();
			if ($lineContent === false) {
				throw $e
					->withMessage("File '$this->fullPath' does not have 'line $this->line'.");
			}

			if ($this->column !== null && !isset($lineContent[$this->column - 1])) {
				throw $e
					->withMessage("File '$this->fullPath' at 'line $this->line' does not have 'column $this->column'.");
			}

			unset($file);
		}
	}

	public function getLastChange(): DateTimeImmutable
	{
		$this->throwIfInvalid(true);

		$time = filemtime($this->fullPath);
		assert($time !== false);

		$datetime = DateTimeImmutable::createFromFormat('U', (string) $time);
		assert($datetime !== false);

		return $datetime;
	}

	public function __toString(): string
	{
		return $this->toString();
	}

	public function __serialize(): array
	{
		return [
			'fullPath' => $this->fullPath,
			'basePath' => $this->basePath,
			'line' => $this->line,
			'column' => $this->column,
		];
	}

	public function __unserialize(array $data): void
	{
		$this->fullPath = $data['fullPath'];
		$this->basePath = $data['basePath'];
		$this->line = $data['line'];
		$this->column = $data['column'];
	}

}
