<?php declare(strict_types = 1);

namespace Tests\Orisai\SourceMap\Doubles;

use DateTimeImmutable;
use DateTimeInterface;
use Orisai\SourceMap\SelfCheckingSource;

final class TestSelfCheckingSource implements SelfCheckingSource
{

	private bool $valid;

	private DateTimeImmutable $lastChange;

	public function __construct(bool $valid, DateTimeImmutable $lastChange)
	{
		$this->valid = $valid;
		$this->lastChange = $lastChange;
	}

	public function toString(): string
	{
		return 'self-checking';
	}

	public function isValid(): bool
	{
		return $this->valid;
	}

	public function getLastChange(): DateTimeImmutable
	{
		return $this->lastChange;
	}

	public function __toString(): string
	{
		return $this->toString();
	}

	public function __serialize(): array
	{
		return [
			'valid' => $this->valid,
			'lastChange' => $this->lastChange->format(DateTimeInterface::ATOM),
		];
	}

	public function __unserialize(array $data): void
	{
		$this->valid = $data['valid'];
		$this->lastChange = DateTimeImmutable::createFromFormat(
			DateTimeInterface::ATOM,
			$data['lastChange'],
		);
	}

}
