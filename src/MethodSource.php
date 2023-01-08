<?php declare(strict_types = 1);

namespace Orisai\SourceMap;

use Orisai\Exceptions\Logic\InvalidState;
use ReflectionException;
use ReflectionMethod;
use Throwable;
use function implode;

/**
 * @readonly
 */
final class MethodSource implements ReflectorSource
{

	private ReflectionMethod $reflector;

	private ?Throwable $failure = null;

	public function __construct(ReflectionMethod $reflector)
	{
		$this->reflector = $reflector;
	}

	public function getClass(): ClassSource
	{
		$this->throwIfInvalid();

		return new ClassSource($this->reflector->getDeclaringClass());
	}

	public function getReflector(): ReflectionMethod
	{
		$this->throwIfInvalid();

		return $this->reflector;
	}

	/**
	 * @param list<string> $parameters
	 */
	public function toString(array $parameters = []): string
	{
		$this->throwIfInvalid();

		$symbol = $this->reflector->isStatic()
			? '::'
			: '->';

		$parametersInline = implode(', ', $parameters);

		return "{$this->getClass()->toString()}$symbol{$this->reflector->getName()}($parametersInline)";
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

	public function __toString(): string
	{
		return $this->toString();
	}

	public function __serialize(): array
	{
		$this->throwIfInvalid();

		return [
			'class' => $this->reflector->getDeclaringClass()->getName(),
			'method' => $this->reflector->getName(),
		];
	}

	public function __unserialize(array $data): void
	{
		try {
			$this->reflector = new ReflectionMethod($data['class'], $data['method']);
		} catch (ReflectionException $exception) {
			$this->failure = $exception;
		}
	}

}
