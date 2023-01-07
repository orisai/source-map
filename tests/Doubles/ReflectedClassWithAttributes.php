<?php declare(strict_types = 1);

namespace Tests\Orisai\SourceMap\Doubles;

// phpcs:disable SlevomatCodingStandard.Classes.RequireSingleLineMethodSignature

#[TestAttribute]
final class ReflectedClassWithAttributes
{

	#[TestAttribute]
	public const Test = 'test';

	#[TestAttribute]
	public string $test = 'test';

	public static string $staticTest = 'test';

	#[TestAttribute]
	public function test(
		#[TestAttribute]
		string $test
	): string
	{
		return $test;
	}

	public static function staticTest(string $test): string
	{
		return $test;
	}

}
