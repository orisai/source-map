<?php declare(strict_types = 1);

namespace Orisai\SourceMap;

interface Source
{

	public function toString(): string;

	public function __toString(): string;

	/**
	 * @return array<int|string, mixed>
	 */
	public function __serialize(): array;

	/**
	 * @param array<int|string, mixed> $data
	 */
	public function __unserialize(array $data): void;

}
