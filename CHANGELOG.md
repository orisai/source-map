# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [Unreleased](https://github.com/orisai/source-map/compare/1.0.3...v1.x)

## [1.0.3](https://github.com/orisai/source-map/compare/1.0.2...1.0.3) - 2025-02-15

### Fixed

- PHPStan generics

## [1.0.2](https://github.com/orisai/source-map/compare/1.0.1...1.0.2) - 2024-12-29

### Added

- Allow PHP 8.4

## [1.0.1](https://github.com/orisai/source-map/compare/1.0.0...1.0.1) - 2024-06-21

### Added

- Allow PHP 8.3
- Allow symfony/filesystem:^7.0.0

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
