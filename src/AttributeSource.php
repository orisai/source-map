<?php declare(strict_types = 1);

namespace Orisai\SourceMap;

use Orisai\Exceptions\Message;
use Orisai\SourceMap\Exception\InvalidSource;
use Reflector;
use function method_exists;
use const PHP_VERSION_ID;

/**
 * @template T of ReflectorSource
 * @implements AboveReflectorSource<T>
 *
 * @readonly
 */
final class AttributeSource implements AboveReflectorSource
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
		$this->throwIfNoAttributes($target, false);
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
		$this->throwIfNoAttributes($this->target, true);

		return "{$this->getTarget()->toString()} attribute";
	}

	public function isValid(): bool
	{
		return $this->target->isValid()
			&& $this->hasAttributes($this->target);
	}

	private function hasAttributes(ReflectorSource $source): bool
	{
		/** @infection-ignore-all */
		if (PHP_VERSION_ID < 8_00_00) {
			return false;
		}

		$reflector = $source->getReflector();

		if (!method_exists($reflector, 'getAttributes')) {
			return false;
		}

		return $reflector->getAttributes() !== [];
	}

	private function throwIfNoAttributes(ReflectorSource $source, bool $deserializing): void
	{
		if ($this->hasAttributes($source)) {
			return;
		}

		$action = $deserializing ? 'Deserializing' : 'Creating';
		$message = Message::create()
			->withContext("$action AttributeSource.")
			->withProblem('Targeted source does not have any attributes.');

		/** @infection-ignore-all */
		if (PHP_VERSION_ID < 8_00_00) {
			$message->with('Hint', 'Attributes are supported since PHP 8.0');
		}

		throw InvalidSource::create($this)
			->withMessage($message);
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
