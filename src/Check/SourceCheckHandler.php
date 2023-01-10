<?php declare(strict_types = 1);

namespace Orisai\SourceMap\Check;

use DateTimeImmutable;
use Orisai\SourceMap\Source;

/**
 * @template T of Source
 */
interface SourceCheckHandler
{

	/**
	 * @return list<class-string<T>>
	 */
	public static function getSupported(): array;

	/**
	 * @param T $source
	 */
	public function isValid(Source $source): bool;

	/**
	 * @param T $source
	 */
	public function getLastChange(Source $source): DateTimeImmutable;

}
