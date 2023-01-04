<?php declare(strict_types = 1);

namespace Orisai\SourceMap;

use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\Exceptions\Message;

/**
 * @phpstan-type TargetType ClassSource|FunctionSource|MethodSource|PropertySource
 *
 * @readonly
 */
final class AnnotationSource implements Source
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

		return "{$this->getTarget()->toString()} annotation";
	}

	public function isValid(): bool
	{
		return $this->target->isValid()
			&& $this->hasDocComment($this->target);
	}

	/**
	 * @phpstan-param TargetType $source
	 */
	private function hasDocComment(Source $source): bool
	{
		return $source->getReflector()->getDocComment() !== false;
	}

	/**
	 * @phpstan-param TargetType $source
	 */
	private function throwIfNoAttributes($source, bool $deserializing): void
	{
		if ($this->hasDocComment($source)) {
			return;
		}

		$action = $deserializing ? 'Deserializing' : 'Creating';
		$message = Message::create()
			->withContext("$action AnnotationSource.")
			->withProblem('Targeted source does not have any annotation.');

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
