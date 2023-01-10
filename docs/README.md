# Source Map

Generate, validate and serialize source maps

## Content

- [Setup](#setup)
- [Quick start](#quick-start)
- [Sources](#sources)
	- [File](#file)
	- [Reflection](#reflection)
	- [Attributes/annotations](#attributes--annotations)
- [Validation](#validation)

## Setup

Install with [Composer](https://getcomposer.org)

```sh
composer require orisai/source-map
```

## Quick start

Each `Orisai\SourceMap\Source` supports validation, serialization and printing of source

```php
use Orisai\SourceMap\AnnotationSource;
use Orisai\SourceMap\ClassSource;
use ReflectionClass;

// Generate (and self-validate)
$source = new AnnotationSource(new ClassSource(new ReflectionClass(AnnotatedClass::class)));

// Print source
echo $source->toString(); // 'AnnotatedClass annotation'

// Serialize and un-serialize
$serialized = serialize($source);
$source = unserialize($serialized);
assert($source instanceof AnnotationSource);

// Ensure source is still valid after un-serialization
$source->isValid(); // bool

// Check last time source has changed
$source->getLastChange(); // DateTimeImmutable
```

## Sources

Track source of your data

### File

```php
use Orisai\SourceMap\FileSource;

$source = new FileSource($path);
$source->getFullPath(); // $path
$source->getRelativePath(); // null
$source->getLine(); // null
$source->getColumn(); // null

$source = new FileSource($path, $basePath, 42, 314);
$source->getFullPath(); // $path
$source->getRelativePath(); // string, e.g. .../relative/path.txt
$source->getLine(); // 42
$source->getColumn(); // 314
```

### Reflection

#### Class source

```php
use Orisai\SourceMap\ClassSource;
use ReflectionClass;

$source = new ClassSource(new ReflectionClass(Example::class));
$source->getReflector(); // ReflectionClass
```

#### Class constant source

```php
use Orisai\SourceMap\ClassConstantSource;
use ReflectionClassConstant;

$source = new ClassConstantSource(new ReflectionClassConstant(Example::class, 'Constant'));
$source->getReflector(); // ReflectionClassConstant
```

#### Property source

```php
use Orisai\SourceMap\PropertySource;
use ReflectionProperty;

$source = new PropertySource(new ReflectionProperty(Example::class, 'property'));
$source->getReflector(); // ReflectionProperty
```

#### Method source

```php
use Orisai\SourceMap\MethodSource;
use ReflectionMethod;

$source = new MethodSource(new ReflectionMethod(Example::class, 'method'));
$source->getReflector(); // ReflectionMethod
```

#### Function source

```php
use Orisai\SourceMap\FunctionSource;
use ReflectionFunction;

$source = new FunctionSource(new ReflectionFunction('functionName'));
$source->getReflector(); // ReflectionFunction
```

#### Parameter source

```php
use Orisai\SourceMap\ParameterSource;
use ReflectionParameter;

$source = new ParameterSource(new ReflectionParameter([Example::class, 'method'], 'parameter'));
$source->getReflector(); // ReflectionParameter
```

### Attributes / annotations

Attributes and annotations are written above most of [reflected sources](#reflection).

#### Attribute source

```php
use Orisai\SourceMap\AttributeSource;
use Orisai\SourceMap\ClassSource;
use ReflectionClass;

$target = new ClassSource(new ReflectionClass(ExampleWithAttributes::class));
$source = new AnnotationSource($target);
$source->getReflector(); // Reflector
$source->getTarget(); // Source (generic ClassSource)
$source->getTarget()->getReflector(); // ReflectionClass
```

#### Annotation source

```php
use Orisai\SourceMap\AnnotationSource;
use Orisai\SourceMap\ClassSource;
use ReflectionClass;

$target = new ClassSource(new ReflectionClass(AnnotatedExample::class));
$source = new AnnotationSource($target);
$source->getReflector(); // Reflector
$source->getTarget(); // Source (generic ClassSource)
$source->getTarget()->getReflector(); // ReflectionClass
```

[Parameter source](#parameter-source) is not supported by annotation source because PHP itself does not support
annotations above parameter.

#### Empty above reflector source

For evidence of a reflector whose annotations/attributes may be used as a source but currently has neither of them.

```php
use Orisai\SourceMap\ClassSource;
use Orisai\SourceMap\EmptyAboveReflectorSource;
use ReflectionClass;

$target = new EmptyAboveReflectorSource(new ReflectionClass(Example::class));
$source = new AnnotationSource($target);
$source->getReflector(); // Reflector
$source->getTarget(); // Source (generic ClassSource)
$source->getTarget()->getReflector(); // ReflectionClass
```

## Validation

Sources which implement `SelfCheckingSource` are self-validated for existence when constructed. Also, when
changed and no longer valid, `Source->isValid()` will return `false` and methods working may even throw `InvalidSource`
exception.

```php
use Orisai\SourceMap\Source;

$source = unserialize($serialized);
assert($source instanceof Source);

if ($source->isValid()) {
	// Calling any method is safe
	$source->toString(); // string
}

if (!$source->isValid()) {
	// Calling any method that requires source throws an exception
	$source->toString(); // throws InvalidSource
}
```

To check any sources, use `SourceChecker` interface

```php
use Orisai\SourceMap\Check\DefaultSourceChecker;
use Orisai\SourceMap\Check\SourceChecker;

$checker = new DefaultSourceChecker();
assert($checker instanceof SourceChecker);

$checker->isValid($source); // bool
$checker->getLastChange($source); // DateTimeImmutable
```

Each checked source type must be known by source checker. To check not self-validating source, implement
a `SourceCheckHandler` and add it to source checker

```php
$checker->addHandler(new ExampleSourceCheckHandler());
```

```php
use DateTimeImmutable;
use Orisai\SourceMap\Check\SourceCheckHandler;
use Orisai\SourceMap\Source;

/**
 * @implements SourceCheckHandler<ExampleSourceOne|ExampleSourceTwo>
 */
final class ExampleSourceCheckHandler implements SourceCheckHandler
{

	public static function getSupported(): array
	{
		return [
			ExampleSourceOne::class,
			ExampleSourceTwo::class,
		];
	}

	public function isValid(Source $source): bool
	{
		if ($source instanceof ExampleSourceOne) {
			// Shenanigans
		}

		assert($source instanceof ExampleSourceTwo);
		// Dark magic
	}

	public function getLastChange(Source $source): DateTimeImmutable
	{
		if ($source instanceof ExampleSourceOne) {
			// Shenanigans
		}

		assert($source instanceof ExampleSourceTwo);
		// Dark magic
	}

}
```
