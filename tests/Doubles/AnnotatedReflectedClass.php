<?php declare(strict_types = 1);

namespace Tests\Orisai\SourceMap\Doubles;

/**
 * Comment
 */
final class AnnotatedReflectedClass
{

	/**
	 * Comment
	 */
	public const Test = 'test';

	/**
	 * Comment
	 */
	public string $test = 'test';

	public static string $staticTest = 'test';

	/**
	 * Comment
	 */
	public function test(string $test): string
	{
		return $test;
	}

	public static function staticTest(string $test): string
	{
		return $test;
	}

}
