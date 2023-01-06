<?php declare(strict_types = 1);

namespace Orisai\SourceMap;

use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\Exceptions\Message;
use Reflector;
use function method_exists;

/**
 * @template T of ReflectorSource
 * @implements AboveReflectorSource<T>
 *
 * @readonly
 */
final class AnnotationSource implements AboveReflectorSource
{

	/** @var T */
	private ReflectorSource $target;

	/**
	 * @param T $target
	 */
	public function __construct(ReflectorSource $target)
	{
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

		return "{$this->getTarget()->toString()} annotation";
	}

	public function isValid(): bool
	{
		return $this->target->isValid()
			&& $this->hasDocComment($this->target);
	}

	private function hasDocComment(ReflectorSource $source): bool
	{
		$reflector = $source->getReflector();

		if (!method_exists($reflector, 'getDocComment')) {
			return false;
		}

		return $reflector->getDocComment() !== false;
	}

	private function throwIfNoAttributes(ReflectorSource $source, bool $deserializing): void
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
