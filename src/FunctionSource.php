<?php declare(strict_types = 1);

namespace Orisai\SourceMap;

use DateTimeImmutable;
use Orisai\SourceMap\Exception\InvalidSource;
use ReflectionException;
use ReflectionFunction;
use Throwable;
use function assert;
use function filemtime;
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

		throw InvalidSource::create($this)
			->withMessage("Deserialization failed due to following error:\n{$this->failure->getMessage()}")
			->withPrevious($this->failure);
	}

	public function getLastChange(): DateTimeImmutable
	{
		$file = $this->getReflector()->getFileName();

		// Internal class
		if ($file === false) {
			$datetime = DateTimeImmutable::createFromFormat('U', '0');
			assert($datetime !== false);

			return $datetime;
		}

		$time = filemtime($file);
		assert($time !== false);

		$datetime = DateTimeImmutable::createFromFormat('U', (string) $time);
		assert($datetime !== false);

		return $datetime;
	}

	public function __toString(): string
	{
		return $this->toString();
	}

	public function __serialize(): array
	{
		$this->throwIfInvalid();

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
