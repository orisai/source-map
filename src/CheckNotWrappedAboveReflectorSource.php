<?php declare(strict_types = 1);

namespace Orisai\SourceMap;

use Orisai\Exceptions\Message;
use Orisai\SourceMap\Exception\InvalidSource;
use Orisai\Utils\Reflection\Classes;
use function get_class;

/**
 * @internal
 */
trait CheckNotWrappedAboveReflectorSource
{

	private function throwIfWrapped(ReflectorSource $target): void
	{
		if (!$target instanceof AboveReflectorSource) {
			return;
		}

		$self = self::class;
		$interface = AboveReflectorSource::class;
		$interfaceShort = Classes::getShortName($interface);
		$class = get_class($target);
		$message = Message::create()
			->withContext("Creating '$self'.")
			->withProblem("Given class '$class' implements '$interface'" .
				" and cannot be wrapped in another '$interfaceShort'.");

		throw InvalidSource::create($this)
			->withMessage($message);
	}

}
