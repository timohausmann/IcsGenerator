# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.1.0] - 2025-12-29

### Added

- `getFile()` now throws an error if the temp file could not be written
- `getFile()` now takes an optional filename argument
- Added descriptions and return types to class methods
- Added more examples to README (store files, multiple events)
- Added CHANGELOG

### Fixed

- Fixed broken example in the README

### Changed

- Module now requires PHP 7
- `getTempFilepath()` now uses random_bytes to reduce chance of colliding file names
- `getTempFilepath()` no longer takes a filename argument

### Removed

- `getFileByID()` – no purpose, consumer should handle caching
- `getDefaultEvent()` – no purpose besides documentation
- `format_timestamp()` – inconsistent to only expose timestamp formatting. If you need to format ICS values require ICS.php yourself or load the module and call `\ICSGen\format_timestamp()` etc. directly

## [2.0.0] - 2024-11-27

### Added

- Support for multiple events (ICSEvent class)
- Map known properties to sanitizers
- Add more VEVENT properties 
- `getDefaultEvent()` method

## [1.2.0] - 2022-02-25

- Initial release