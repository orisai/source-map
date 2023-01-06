<?php declare(strict_types = 1);

namespace Orisai\SourceMap;

use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\Exceptions\Message;
use Orisai\Utils\Reflection\Classes;
use function get_class;

trait CheckNotWrappedAboveReflectorSource
{

	public function throwIfWrapped(ReflectorSource $target): void
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

		throw InvalidArgument::create()
			->withMessage($message);
	}

}
