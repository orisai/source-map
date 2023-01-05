<?php declare(strict_types = 1);

namespace Orisai\SourceMap;

use Orisai\Exceptions\Logic\InvalidState;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionParameter;
use Throwable;
use function assert;

/**
 * @readonly
 */
final class ParameterSource implements ReflectorSource
{

	private ReflectionParameter $reflector;

	private ?Throwable $failure = null;

	public function __construct(ReflectionParameter $reflector)
	{
		$this->reflector = $reflector;
	}

	/**
	 * @return FunctionSource|MethodSource
	 */
	public function getFunction(): Source
	{
		$this->throwIfInvalid();

		$reflector = $this->reflector->getDeclaringFunction();

		if ($reflector instanceof ReflectionMethod) {
			return new MethodSource($reflector);
		}

		assert($reflector instanceof ReflectionFunction);

		return new FunctionSource($reflector);
	}

	public function getReflector(): ReflectionParameter
	{
		$this->throwIfInvalid();

		return $this->reflector;
	}

	public function toString(): string
	{
		$this->throwIfInvalid();

		return $this->getFunction()->toString(
			[
				$this->reflector->getName(),
			],
		);
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
		$class = $this->reflector->getDeclaringClass();

		return [
			'class' => $class !== null ? $class->getName() : null,
			'function' => $this->reflector->getDeclaringFunction()->getName(),
			'parameter' => $this->reflector->getName(),
		];
	}

	public function __unserialize(array $data): void
	{
		$class = $data['class'];
		$function = $data['function'];
		$parameter = $data['parameter'];

		try {
			$this->reflector = $class !== null
				? new ReflectionParameter([$class, $function], $parameter)
				: new ReflectionParameter($function, $parameter);
		} catch (ReflectionException $exception) {
			$this->failure = $exception;
		}
	}

}
