<?php declare(strict_types = 1);

namespace Orisai\SourceMap;

use Reflector;

/**
 * @readonly
 */
final class EmptyAboveReflectorSource implements AboveReflectorSource
{

	private ReflectorSource $target;

	public function __construct(ReflectorSource $target)
	{
		$this->target = $target;
	}

	public function getTarget(): ReflectorSource
	{
		return $this->target;
	}

	public function getReflector(): Reflector
	{
		return $this->target->getReflector();
	}

	public function toString(): string
	{
		return "{$this->getTarget()->toString()} empty source";
	}

	public function isValid(): bool
	{
		return $this->target->isValid();
	}

	public function __serialize(): array
	{
		return [
			'target' => $this->target,
		];
	}

	public function __unserialize(array $data): void
	{
		$this->target = $data['target'];
	}

}