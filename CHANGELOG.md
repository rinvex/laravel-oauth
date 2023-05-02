# Rinvex OAuth Change Log

All notable changes to this project will be documented in this file.

This project adheres to [Semantic Versioning](CONTRIBUTING.md).


## [v4.1.0] - 2023-05-02
- a16518d: Add support for Laravel v11, and drop support for Laravel v9
- d716e6e: Upgrade league/oauth2-server to v8.5 from v8.3
- da81753: Upgrade lcobucci/jwt to v5.0 from v4.1
- 678aed0: Upgrade nesbot/carbon to v2.62 from v2.56
- 9e4493e: Upgrade nyholm/psr7 to v1.7 from v1.5
- 3bca59d: Upgrade firebase/php-jwt to v6.4 from v6.0
- e32b8a1: Upgrade symfony/psr-http-message-bridge to v2.2 from v2.1
- 391e31a: Update phpunit to v10.1 from v9.5
- 170ba87: Apply fixes from StyleCI (#11)

## [v4.0.1] - 2023-01-10
- Simplify encryption key paths
- Fix missing AuthorizationServer encryption key parameter
- Fix user identifier to be like that 'admin:123' where admin is user_type & 123 is user_id

## [v4.0.0] - 2023-01-09
- Tweak artisan commands registration
- Drop PHP v8.0 support and update composer dependencies
- Utilize PHP 8.1 attributes feature for artisan commands

## [v3.1.2] - 2022-08-30
- Update exists and unique validation rules to use models instead of tables
- Update composer dependencies symfony/http-foundation to ^6.1.0 from ^6.0.0

## [v3.1.1] - 2022-02-16
- Fix compatibility issues with firebase/php-jwt v6
- Fix missing import and wrong phpseclib namespace

## [v3.1.0] - 2022-02-14
- Update composer dependencies to Laravel v9
- Sync with Laravel Passport v10.3.1
- Add support for model HasFactory

## [v3.0.0] - 2021-08-22
- Drop PHP v7 support, and upgrade rinvex package dependencies to next major version

## [v2.0.7] - 2021-06-20
- Fix namespace naming convention

## [v2.0.6] - 2021-05-24
- Merge rules instead of resetting, to allow adequate model override

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

[v4.1.0]: https://github.com/rinvex/laravel-oauth/compare/v4.0.1...v4.1.0
[v4.0.1]: https://github.com/rinvex/laravel-oauth/compare/v4.0.0...v4.0.1
[v4.0.0]: https://github.com/rinvex/laravel-oauth/compare/v3.1.2...v4.0.0
[v3.1.2]: https://github.com/rinvex/laravel-oauth/compare/v3.1.1...v3.1.2
[v3.1.1]: https://github.com/rinvex/laravel-oauth/compare/v3.1.0...v3.1.1
[v3.1.0]: https://github.com/rinvex/laravel-oauth/compare/v3.0.0...v3.1.0
[v3.0.0]: https://github.com/rinvex/laravel-oauth/compare/v2.0.7...v3.0.0
[v2.0.7]: https://github.com/rinvex/laravel-oauth/compare/v2.0.6...v2.0.7
[v2.0.6]: https://github.com/rinvex/laravel-oauth/compare/v2.0.5...v2.0.6
[v2.0.5]: https://github.com/rinvex/laravel-oauth/compare/v2.0.4...v2.0.5
[v2.0.4]: https://github.com/rinvex/laravel-oauth/compare/v2.0.3...v2.0.4
[v2.0.3]: https://github.com/rinvex/laravel-oauth/compare/v2.0.2...v2.0.3
[v2.0.2]: https://github.com/rinvex/laravel-oauth/compare/v2.0.1...v2.0.2
[v2.0.1]: https://github.com/rinvex/laravel-oauth/compare/v2.0.0...v2.0.1
[v2.0.0]: https://github.com/rinvex/laravel-oauth/compare/v1.0.1...v2.0.0
[v1.0.1]: https://github.com/rinvex/laravel-oauth/compare/v1.0.0...v1.0.1
