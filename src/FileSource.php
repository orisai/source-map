<?php declare(strict_types = 1);

namespace Orisai\SourceMap;

use Symfony\Component\Filesystem\Path;
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
		return is_file($this->fullPath);
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
