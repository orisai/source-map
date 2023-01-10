<?php declare(strict_types = 1);

namespace Orisai\SourceMap\Check;

use DateTimeImmutable;
use Orisai\Exceptions\Logic\InvalidState;
use Orisai\Exceptions\Message;
use Orisai\SourceMap\SelfCheckingSource;
use Orisai\SourceMap\Source;
use function get_class;
use function is_a;

final class DefaultSourceChecker implements SourceChecker
{

	/** @var list<SourceCheckHandler<Source>> */
	private array $handlers = [];

	/**
	 * @param SourceCheckHandler<Source> $handler
	 */
	public function addHandler(SourceCheckHandler $handler): void
	{
		$this->handlers[] = $handler;
	}

	public function isValid(Source $source): bool
	{
		if ($source instanceof SelfCheckingSource) {
			return $source->isValid();
		}

		foreach ($this->handlers as $handler) {
			foreach ($handler::getSupported() as $supported) {
				if (is_a($source, $supported)) {
					return $handler->isValid($source);
				}
			}
		}

		$class = get_class($source);
		$selfCheckInterface = SelfCheckingSource::class;
		$message = Message::create()
			->withContext("Checking whether '$class' is a valid source.")
			->withProblem('No handler handles this source.')
			->withSolution("Add handler for the source or make the source implement '$selfCheckInterface'.");

		throw InvalidState::create()
			->withMessage($message);
	}

	public function getLastChange(Source $source): DateTimeImmutable
	{
		if ($source instanceof SelfCheckingSource) {
			return $source->getLastChange();
		}

		foreach ($this->handlers as $handler) {
			foreach ($handler::getSupported() as $supported) {
				if (is_a($source, $supported)) {
					return $handler->getLastChange($source);
				}
			}
		}

		$class = get_class($source);
		$selfCheckInterface = SelfCheckingSource::class;
		$message = Message::create()
			->withContext("Getting last change of a source '$class'.")
			->withProblem('No handler handles this source.')
			->withSolution("Add handler for the source or make the source implement '$selfCheckInterface'.");

		throw InvalidState::create()
			->withMessage($message);
	}

}
