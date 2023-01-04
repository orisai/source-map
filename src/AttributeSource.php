<?php declare(strict_types = 1);

namespace Orisai\SourceMap;

use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\Exceptions\Message;
use const PHP_VERSION_ID;

/**
 * @phpstan-type TargetType ClassConstantSource|ClassSource|FunctionSource|MethodSource|ParameterSource|PropertySource
 *
 * @readonly
 */
final class AttributeSource implements Source
{

	/** @phpstan-var TargetType */
	private Source $target;

	/**
	 * @phpstan-param TargetType $target
	 */
	public function __construct(Source $target)
	{
		$this->throwIfNoAttributes($target, false);
		$this->target = $target;
	}

	/**
	 * @phpstan-return TargetType
	 */
	public function getTarget(): Source
	{
		return $this->target;
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

	/**
	 * @phpstan-param TargetType $source
	 */
	private function hasAttributes(Source $source): bool
	{
		if (PHP_VERSION_ID < 8_00_00) {
			return false;
		}

		return $source->getReflector()->getAttributes() !== [];
	}

	/**
	 * @phpstan-param TargetType $source
	 */
	private function throwIfNoAttributes($source, bool $deserializing): void
	{
		if ($this->hasAttributes($source)) {
			return;
		}

		$action = $deserializing ? 'Deserializing' : 'Creating';
		$message = Message::create()
			->withContext("$action AttributeSource.")
			->withProblem('Targeted source does not have any attributes.');

		if (PHP_VERSION_ID < 8_00_00) {
			$message->with('Hint', 'Attributes are supported since PHP 8.0');
		}

		throw InvalidArgument::create()
			->withMessage($message);
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
