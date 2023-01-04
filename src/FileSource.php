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

	public function __construct(string $fullPath, ?string $basePath = null)
	{
		$this->fullPath = $fullPath;
		$this->basePath = $basePath;
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

	public function toString(): string
	{
		$relative = $this->getRelativePath();

		return $relative !== null
			? ".../$relative"
			: $this->getFullPath();
	}

	public function isValid(): bool
	{
		return is_file($this->fullPath);
	}

	public function __serialize(): array
	{
		return [
			'fullPath' => $this->fullPath,
			'basePath' => $this->basePath,
		];
	}

	public function __unserialize(array $data): void
	{
		$this->fullPath = $data['fullPath'];
		$this->basePath = $data['basePath'];
	}

}
