<?php declare(strict_types = 1);

namespace Orisai\SourceMap;

use Reflector;

interface ReflectorSource extends SelfCheckingSource
{

	public function getReflector(): Reflector;

}
