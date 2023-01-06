<?php declare(strict_types = 1);

namespace Orisai\SourceMap;

interface AboveReflectorSource extends ReflectorSource
{

	public function getTarget(): ReflectorSource;

}
