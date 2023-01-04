<h1 align="center">
	<img src="https://github.com/orisai/.github/blob/main/images/repo_title.png?raw=true" alt="Orisai"/>
	<br/>
	Source Map
</h1>

<p align="center">
    Generate, validate and serialize source maps
</p>

<p align="center">
	📄 Check out our <a href="docs/README.md">documentation</a>.
</p>

<p align="center">
	💸 If you like Orisai, please <a href="https://orisai.dev/sponsor">make a donation</a>. Thank you!
</p>

<p align="center">
	<a href="https://github.com/orisai/source-map/actions?query=workflow%3Aci">
		<img src="https://github.com/orisai/source-map/workflows/ci/badge.svg">
	</a>
	<a href="https://coveralls.io/r/orisai/source-map">
		<img src="https://badgen.net/coveralls/c/github/orisai/source-map/v1.x?cache=300">
	</a>
	<a href="https://dashboard.stryker-mutator.io/reports/github.com/orisai/source-map/v1.x">
		<img src="https://badge.stryker-mutator.io/github.com/orisai/source-map/v1.x">
	</a>
	<a href="https://packagist.org/packages/orisai/source-map">
		<img src="https://badgen.net/packagist/dt/orisai/source-map?cache=3600">
	</a>
	<a href="https://packagist.org/packages/orisai/source-map">
		<img src="https://badgen.net/packagist/v/orisai/source-map?cache=3600">
	</a>
	<a href="https://choosealicense.com/licenses/mpl-2.0/">
		<img src="https://badgen.net/badge/license/MPL-2.0/blue?cache=3600">
	</a>
<p>

##

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
```
