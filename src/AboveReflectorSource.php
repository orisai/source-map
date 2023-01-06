<?php declare(strict_types = 1);

namespace Orisai\SourceMap;

/**
 * @template T of ReflectorSource
 */
interface AboveReflectorSource extends ReflectorSource
{

	/**
	 * @return T
	 */
	public function getTarget(): ReflectorSource;

}
