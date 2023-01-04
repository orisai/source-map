<?php declare(strict_types = 1);

namespace Orisai\SourceMap;

/**
 * @readonly
 */
final class LineColumnSource implements Source
{

	private FileSource $file;

	/** @var int<1, max> */
	private int $line;

	/** @var int<1, max>|null */
	private ?int $column;

	/**
	 * @param int<1, max>      $line
	 * @param int<1, max>|null $column
	 */
	public function __construct(FileSource $file, int $line, ?int $column = null)
	{
		$this->file = $file;
		$this->line = $line;
		$this->column = $column;
	}

	public function getFile(): FileSource
	{
		return $this->file;
	}

	/**
	 * @return int<1, max>
	 */
	public function getLine(): int
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
		return "{$this->getFile()->toString()}:{$this->getLine()}"
			. ($this->column !== null ? ":$this->column" : '');
	}

	public function isValid(): bool
	{
		return $this->file->isValid();
	}

	public function __serialize(): array
	{
		return [
			'file' => $this->file,
			'line' => $this->line,
			'column' => $this->column,
		];
	}

	public function __unserialize(array $data): void
	{
		$this->file = $data['file'];
		$this->line = $data['line'];
		$this->column = $data['column'];
	}

}
