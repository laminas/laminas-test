# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 3.4.0 - 2020-02-07

### Added

- [#10](https://github.com/laminas/laminas-test/pull/10) Adds support for PHPUnit 9

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 3.3.0 - 2019-06-11

### Added

- [zendframework/zend-test#76](https://github.com/zendframework/zend-test/pull/76) adds support for PHPUnit 8
  
  Undesired PHPUnit update to ^8.0 can happen on PHP 7.2 and newer when relying
  on PHPUnit installation as an indirect dependency via laminas-test.
  Please always declare direct dependency on `phpunit/phpunit` with suitable
  versions alongside with `laminas/laminas-test`.
  
  PHPUnit 8 incompatible test suite typically would error after the update
  with messages like "Fatal error: Declaration of *::setUp() must be
  compatible with *::setUp(): void" for any of the following methods:

  - `setUpBeforeClass()`
  - `tearDownAfterClass()`
  - `setUp()`
  - `tearDown()`

  Following command can be used to declare explicit dependency on older PHPUnit versions:
  ```bash
  composer require --dev phpunit/phpunit:"^7.5.12 || ^6.5.14 || ^5.7.14" --update-with-dependencies
  ```

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 3.2.2 - 2019-01-08

### Added

- [zendframework/zend-test#75](https://github.com/zendframework/zend-test/pull/75) adds support for PHP 7.3.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-test#74](https://github.com/zendframework/zend-test/pull/74) reverts changes introduced in version 3.2.1 to how superglobals are reset
  between tests, primarily by fixing the root problem -- base URL detection --
  by requiring a laminas-http version that fixes that detection.

## 3.2.1 - 2018-12-10

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-test#70](https://github.com/zendframework/zend-test/pull/70) fixes a memory leak in controller test cases.

- [zendframework/zend-test#66](https://github.com/zendframework/zend-test/pull/66) Fixes globals not
  cleared for controller tests

## 3.2.0 - 2018-04-07

### Added

- [zendframework/zend-test#60](https://github.com/zendframework/zend-test/pull/60) Added support for
  PHPUnit 7
- [zendframework/zend-test#65](https://github.com/zendframework/zend-test/pull/65) Added support for
  query parameters in DELETE request in AbstractControllerTestCase

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-test#63](https://github.com/zendframework/zend-test/pull/63) Fixed compatibility
  with PHP 7.2

## 3.1.1 - 2017-10-29

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-test#55](https://github.com/zendframework/zend-test/pull/55) Fixes compatibility
  with PHPUnit 5.7.23 where empty expected exception message no longer means
  message is not checked.
- [zendframework/zend-test#49](https://github.com/zendframework/zend-test/pull/49) Fixes missing alias
  for compatibility with PHPUnit <6.0

## 3.1.0 - 2017-05-01

### Added

- [zendframework/zend-test#40](https://github.com/zendframework/zend-test/pull/40) and
  [zendframework/zend-test#48](https://github.com/zendframework/zend-test/pull/48) add support for
  the PHPUnit 6 series, while retaining support for PHPUnit 4 and 5.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 3.0.2 - 2016-09-06

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-test#33](https://github.com/zendframework/zend-test/pull/33) fixes
  `queryContentRegexAssertion()` (used by `assertQueryContentRegex()` and
  `assertXpathQueryContentRegex()`) properly checks all matching nodes for
  content matching the regular expression, instead of only the first. The
  prevents false negative assertions from occuring.
- [zendframework/zend-test#21](https://github.com/zendframework/zend-test/pull/21) updates the
  `sebastian/version` dependency to also allow v2.0 releases.
- [zendframework/zend-test#31](https://github.com/zendframework/zend-test/pull/31) fixes an issue with
  the `AbstractControllerTestCase` when used to test a console request.
  Previously, routes with multiple literal flags were never matched; they now
  are.

## 3.0.1 - 2016-06-15

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-test#26](https://github.com/zendframework/zend-test/pull/26) fixes how
  `$traceErrors` works under PHP 7 and PHPUnit 5. Any laminas-test-specific
  assertion failures now append a list of all exception messages to the base
  message when the flag is enabled.

## 3.0.0 - 2016-05-31

### Added

- This release adds support for laminas-mvc v3.

### Deprecated

- Nothing.

### Removed

- This release removes support for PHP versions `< 5.6`.
- This release removes support for laminas-mvc v2.

### Fixed

- Nothing.

## 2.6.2 - TBD

### Added

- [zendframework/zend-test#22](https://github.com/zendframework/zend-test/pull/22) adds and publishes
  the documentation to https://docs.laminas.dev/laminas-test/

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.6.1 - 2016-03-02

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-test#20](https://github.com/zendframework/zend-test/pull/20) updates the laminas-mvc
  requirement to 2.7.1, ensuring deprecation notices will not occur in the
  majority of circumstances.

## 2.6.0 - 2016-03-01

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-test#19](https://github.com/zendframework/zend-test/pull/19) updates the
  code to be forwards compatible with:
  - laminas-eventmanager v3
  - laminas-servicemanager v3
  - laminas-stdlib v3
  - laminas-mvc v2.7

## 2.5.3 - 2016-03-01

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-test#6](https://github.com/zendframework/zend-test/pull/6) updates the
  `AbstractControllerTestCase` to mark a test as failed if no route match occurs
  in a number of assertions that require a route match.
- [zendframework/zend-test#7](https://github.com/zendframework/zend-test/pull/7) modifies the `reset()`
  method of the `AbstractControllerTestCase` to prevent rewriting the
  `$_SESSION` superglobal if it has not previously been enabled.

## 2.5.2 - 2015-12-09

### Added

- [zendframework/zend-test#4](https://github.com/zendframework/zend-test/pull/4) PHPUnit v5 Support.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.
