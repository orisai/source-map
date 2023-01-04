<?php declare(strict_types = 1);

namespace Orisai\SourceMap;

use Orisai\Exceptions\Logic\InvalidState;
use ReflectionClass;
use ReflectionException;
use Throwable;

/**
 * @readonly
 */
final class ClassSource implements Source
{

	/** @var ReflectionClass<object> */
	private ReflectionClass $reflector;

	private ?Throwable $failure = null;

	/**
	 * @param ReflectionClass<object> $reflector
	 */
	public function __construct(ReflectionClass $reflector)
	{
		$this->reflector = $reflector;
	}

	/**
	 * @return ReflectionClass<object>
	 */
	public function getReflector(): ReflectionClass
	{
		$this->throwIfInvalid();

		return $this->reflector;
	}

	public function toString(): string
	{
		$this->throwIfInvalid();

		return $this->reflector->getName();
	}

	public function isValid(): bool
	{
		return $this->failure === null;
	}

	private function throwIfInvalid(): void
	{
		if ($this->failure === null) {
			return;
		}

		throw InvalidState::create()
			->withMessage("Deserialization failed due to following error:\n{$this->failure->getMessage()}")
			->withPrevious($this->failure);
	}

	public function __serialize(): array
	{
		$this->throwIfInvalid();

		return [
			'class' => $this->reflector->getName(),
		];
	}

	public function __unserialize(array $data): void
	{
		try {
			$this->reflector = new ReflectionClass($data['class']);
		} catch (ReflectionException $exception) {
			$this->failure = $exception;
		}
	}

}
