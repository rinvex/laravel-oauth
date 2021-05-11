# Rinvex OAuth Change Log

All notable changes to this project will be documented in this file.

This project adheres to [Semantic Versioning](CONTRIBUTING.md).


## [v2.0.5] - 2021-05-11
- Fix constructor initialization order (fill attributes should come next after merging fillables & rules)
- Drop old MySQL versions support that doesn't support json columns

## [v2.0.4] - 2021-04-11
- Update composer dependency lcobucci/jwt to v4.1 for PHP v8 compatibility
- Fix readme code samples markdown format
- Fix couple typos in documentation
- Update docs
- Tweak `revokeAccessToken` method
- Tweak oauth server default scope assignment
- Add documentation

## [v2.0.3] - 2021-02-28
- Override grant classes and validate user
- Override `ClientCredentialsGrant` and move grants to it's own namespace
- Add autoincrement and timestamps for refresh token, access token, and auth codes
- Rename `id` column to `identifier` for refresh token, access token, and auth codes, and drop primary index, just make unique
- Refactor "scopes" and use "abilities" instead
- Add support for hashids, and `unhashId` if used
- Move `keyPath` method to KeysCommand
- Refactor provider to user_type

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

[v2.0.5]: https://github.com/rinvex/laravel-oauth/compare/v2.0.4...v2.0.5
[v2.0.4]: https://github.com/rinvex/laravel-oauth/compare/v2.0.3...v2.0.4
[v2.0.3]: https://github.com/rinvex/laravel-oauth/compare/v2.0.2...v2.0.3
[v2.0.2]: https://github.com/rinvex/laravel-oauth/compare/v2.0.1...v2.0.2
[v2.0.1]: https://github.com/rinvex/laravel-oauth/compare/v2.0.0...v2.0.1
[v2.0.0]: https://github.com/rinvex/laravel-oauth/compare/v1.0.1...v2.0.0
[v1.0.1]: https://github.com/rinvex/laravel-oauth/compare/v1.0.0...v1.0.1
