<?php declare(strict_types = 1);

namespace Tests\Orisai\SourceMap\Doubles;

use Orisai\SourceMap\ReflectorSource;

final class TestReflectorSource implements ReflectorSource
{

	private TestReflector $reflector;

	public function __construct()
	{
		$this->reflector = new TestReflector();
	}

	public function getReflector(): TestReflector
	{
		return $this->reflector;
	}

	public function toString(): string
	{
		return 'string';
	}

	public function isValid(): bool
	{
		return true;
	}

	public function __serialize(): array
	{
		return [];
	}

	public function __unserialize(array $data): void
	{
		$this->reflector = new TestReflector();
	}

}
