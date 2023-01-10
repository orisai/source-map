# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [Unreleased](https://github.com/orisai/source-map/compare/1.0.0...HEAD)

## [1.0.0](https://github.com/orisai/source-map/releases/tag/1.0.0) - 2023-01-10

### Added

- `Source` interface
	- `SelfCheckingSource` interface
- `SelfCheckingSource` interface
	- `ReflectorSource` interface
	- `FileSource`
- `ReflectorSource` interface
	- `AboveReflectorSource` interface
	- `ClassConstantSource`
	- `ClassSource`
	- `FunctionSource`
	- `MethodSource`
	- `ParameterSource`
	- `PropertySource`
- `AboveReflectorSource` interface
	- `AnnotationSource`
	- `AttributeSource`
	- `EmptyAboveReflectorSource`
- `SourceChecker`
	- `SourceCheckHandler` interface
	- `DefaultSourceChecker`
- `InvalidSource` exception
