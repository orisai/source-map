<?php declare(strict_types = 1);

namespace Orisai\SourceMap;

use Orisai\SourceMap\Exception\InvalidSource;
use ReflectionClassConstant;
use ReflectionException;
use Throwable;

/**
 * @readonly
 */
final class ClassConstantSource implements ReflectorSource
{

	private ReflectionClassConstant $reflector;

	private ?Throwable $failure = null;

	public function __construct(ReflectionClassConstant $reflector)
	{
		$this->reflector = $reflector;
	}

	public function getClass(): ClassSource
	{
		$this->throwIfInvalid();

		return new ClassSource($this->reflector->getDeclaringClass());
	}

	public function getReflector(): ReflectionClassConstant
	{
		$this->throwIfInvalid();

		return $this->reflector;
	}

	public function toString(): string
	{
		return "{$this->getClass()->toString()}::{$this->reflector->getName()}";
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

		throw InvalidSource::create($this)
			->withMessage("Deserialization failed due to following error:\n{$this->failure->getMessage()}")
			->withPrevious($this->failure);
	}

	public function __toString(): string
	{
		return $this->toString();
	}

	public function __serialize(): array
	{
		$this->throwIfInvalid();

		return [
			'class' => $this->reflector->getDeclaringClass()->getName(),
			'constant' => $this->reflector->getName(),
		];
	}

	public function __unserialize(array $data): void
	{
		try {
			$this->reflector = new ReflectionClassConstant($data['class'], $data['constant']);
		} catch (ReflectionException $exception) {
			$this->failure = $exception;
		}
	}

}
