<?php declare(strict_types = 1);

namespace Orisai\SourceMap;

use DateTimeImmutable;
use Orisai\SourceMap\Exception\InvalidSource;
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

		throw $this->failure;
	}

	public function getLastChange(): DateTimeImmutable
	{
		return $this->getFunction()->getLastChange();
	}

	public function __toString(): string
	{
		return $this->toString();
	}

	public function __serialize(): array
	{
		$this->throwIfInvalid();

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
			$message = $exception->getMessage();
			if ($message === 'The parameter specified by its name could not be found') {
				$message = "Parameter {$data['class']}::{$data['function']}({$data['parameter']}) does not exist";
			}

			$this->failure = InvalidSource::create($this)
				->withMessage("Deserialization failed due to following error:\n$message")
				->withPrevious($exception);
		}
	}

}
