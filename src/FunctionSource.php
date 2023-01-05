<?php declare(strict_types = 1);

namespace Orisai\SourceMap;

use Orisai\Exceptions\Logic\InvalidState;
use ReflectionException;
use ReflectionFunction;
use Throwable;
use function implode;

/**
 * @readonly
 */
final class FunctionSource implements ReflectorSource
{

	private ReflectionFunction $reflector;

	private ?Throwable $failure = null;

	public function __construct(ReflectionFunction $reflector)
	{
		$this->reflector = $reflector;
	}

	public function getReflector(): ReflectionFunction
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

		$parametersInline = implode(', ', $parameters);

		return "{$this->reflector->getName()}($parametersInline)";
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
		return [
			'function' => $this->reflector->getName(),
		];
	}

	public function __unserialize(array $data): void
	{
		try {
			$this->reflector = new ReflectionFunction($data['function']);
		} catch (ReflectionException $exception) {
			$this->failure = $exception;
		}
	}

}
