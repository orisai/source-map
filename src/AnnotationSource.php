<?php declare(strict_types = 1);

namespace Orisai\SourceMap;

use Orisai\Exceptions\Message;
use Orisai\SourceMap\Exception\InvalidSource;
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

	use CheckNotWrappedAboveReflectorSource;

	/** @var T */
	private ReflectorSource $target;

	/**
	 * @param T $target
	 */
	public function __construct(ReflectorSource $target)
	{
		$this->throwIfWrapped($target);
		$this->throwIfNoDocComment($target, false);
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
		$this->throwIfNoDocComment($this->target, true);

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

	private function throwIfNoDocComment(ReflectorSource $source, bool $deserializing): void
	{
		if ($this->hasDocComment($source)) {
			return;
		}

		$action = $deserializing ? 'Deserializing' : 'Creating';
		$message = Message::create()
			->withContext("$action AnnotationSource.")
			->withProblem('Targeted source does not have any annotations.');

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
