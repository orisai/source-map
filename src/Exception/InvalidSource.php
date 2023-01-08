<?php declare(strict_types = 1);

namespace Orisai\SourceMap\Exception;

use Orisai\Exceptions\LogicalException;
use Orisai\SourceMap\Source;

final class InvalidSource extends LogicalException
{

	private Source $source;

	public static function create(Source $source): self
	{
		$self = new self();
		$self->source = $source;

		return $self;
	}

	public function getSource(): Source
	{
		return $this->source;
	}

}
