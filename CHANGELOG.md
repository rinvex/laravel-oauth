# Rinvex OAuth Change Log

All notable changes to this project will be documented in this file.

This project adheres to [Semantic Versioning](CONTRIBUTING.md).


## [v2.0.2] - 2021-02-11
- Return client hashed id instead of standard numeric
- Fix wrong access token query and enforce consistency
- Add validation rules to artisan commands
- Fix user provider features and conventions
- Define morphMany parameters explicitly
- Remove testing features `actingAsClient` and `actingAs`
- Set default value for `is_revoked` as false
- Require `rinvex/tmp-josephsilber-bouncer` composer dependency
- Expect hashed client ID, and resolve it
- Simplify service provider model registration into IoC
- Add missing config environment variables
- Enable StyleCI risky mode

## [v2.0.1] - 2020-12-25
- Add support for PHP v8

## [v2.0.0] - 2020-12-22
- Upgrade to Laravel v8

## [v1.0.1] - 2020-12-12
- Update composer dependencies
- Fix code style and enforce consistency

## v1.0.0 - 2020-12-12
- Tag first release

[v2.0.2]: https://github.com/rinvex/laravel-oauth/compare/v2.0.1...v2.0.2
[v2.0.1]: https://github.com/rinvex/laravel-oauth/compare/v2.0.0...v2.0.1
[v2.0.0]: https://github.com/rinvex/laravel-oauth/compare/v1.0.1...v2.0.0
[v1.0.1]: https://github.com/rinvex/laravel-oauth/compare/v1.0.0...v1.0.1
