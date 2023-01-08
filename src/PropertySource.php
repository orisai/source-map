<?php declare(strict_types = 1);

namespace Orisai\SourceMap;

use Orisai\SourceMap\Exception\InvalidSource;
use ReflectionException;
use ReflectionProperty;
use Throwable;

/**
 * @readonly
 */
final class PropertySource implements ReflectorSource
{

	private ReflectionProperty $reflector;

	private ?Throwable $failure = null;

	public function __construct(ReflectionProperty $reflector)
	{
		$this->reflector = $reflector;
	}

	public function getClass(): ClassSource
	{
		$this->throwIfInvalid();

		return new ClassSource($this->reflector->getDeclaringClass());
	}

	public function getReflector(): ReflectionProperty
	{
		$this->throwIfInvalid();

		return $this->reflector;
	}

	public function toString(): string
	{
		$this->throwIfInvalid();

		$symbol = $this->reflector->isStatic()
			? '::'
			: '->';

		return "{$this->getClass()->toString()}$symbol\${$this->reflector->getName()}";
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
			'property' => $this->reflector->getName(),
		];
	}

	public function __unserialize(array $data): void
	{
		try {
			$this->reflector = new ReflectionProperty($data['class'], $data['property']);
		} catch (ReflectionException $exception) {
			$this->failure = $exception;
		}
	}

}
