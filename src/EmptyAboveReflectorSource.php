<?php declare(strict_types = 1);

namespace Orisai\SourceMap;

use DateTimeImmutable;
use Reflector;

/**
 * @template T of ReflectorSource
 * @implements AboveReflectorSource<T>
 *
 * @readonly
 */
final class EmptyAboveReflectorSource implements AboveReflectorSource
{

	use CheckNotWrappedAboveReflectorSource;

	/** @var T */
	private ReflectorSource $target;

	/**
	 * @param T $target
	 */
	public function __construct(ReflectorSource $target)
	{
		$this->throwIfWrapped($target);
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

	public function getLastChange(): DateTimeImmutable
	{
		return $this->getTarget()->getLastChange();
	}

	public function __toString(): string
	{
		return $this->toString();
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
