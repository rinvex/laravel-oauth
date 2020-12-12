# Rinvex OAuth

**Rinvex OAuth** is an OAuth2 server and API authentication package that is simple and enjoyable to use. It is based on and built upon [Laravel Passport](https://laravel.com/docs/8.x/passport).

[![Packagist](https://img.shields.io/packagist/v/rinvex/laravel-oauth.svg?label=Packagist&style=flat-square)](https://packagist.org/packages/rinvex/laravel-oauth)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/rinvex/laravel-oauth.svg?label=Scrutinizer&style=flat-square)](https://scrutinizer-ci.com/g/rinvex/laravel-oauth/)
[![Travis](https://img.shields.io/travis/rinvex/laravel-oauth.svg?label=TravisCI&style=flat-square)](https://travis-ci.org/rinvex/laravel-oauth)
[![StyleCI](https://styleci.io/repos/98953486/shield)](https://styleci.io/repos/98953486)
[![License](https://img.shields.io/packagist/l/rinvex/laravel-oauth.svg?label=License&style=flat-square)](https://github.com/rinvex/laravel-oauth/blob/develop/LICENSE)


## Installation

1. Install the package via composer:
    ```shell
    composer require rinvex/laravel-oauth
    ```

2. Publish resources (migrations and config files):
    ```shell
    php artisan rinvex:publish:oauth
    ```

3. Execute migrations via the following command:
    ```shell
    php artisan rinvex:migrate:oauth
    ```

4. Done!

## Usage

Given it's built upon [Laravel Passport](https://laravel.com/docs/8.x/passport), it's following most of the included features, and could be used in a very similar way, but still it has some differences. Documentation is not ready yet, so rely on official [Laravel Passport](https://laravel.com/docs/8.x/passport) docs for now, and make sure to check the code for any differences.


## Changelog

Refer to the [Changelog](CHANGELOG.md) for a full history of the project.


## Support

The following support channels are available at your fingertips:

- [Chat on Slack](https://bit.ly/rinvex-slack)
- [Help on Email](mailto:help@rinvex.com)
- [Follow on Twitter](https://twitter.com/rinvex)


## Contributing & Protocols

Thank you for considering contributing to this project! The contribution guide can be found in [CONTRIBUTING.md](CONTRIBUTING.md).

Bug reports, feature requests, and pull requests are very welcome.

- [Versioning](CONTRIBUTING.md#versioning)
- [Pull Requests](CONTRIBUTING.md#pull-requests)
- [Coding Standards](CONTRIBUTING.md#coding-standards)
- [Feature Requests](CONTRIBUTING.md#feature-requests)
- [Git Flow](CONTRIBUTING.md#git-flow)


## Security Vulnerabilities

If you discover a security vulnerability within this project, please send an e-mail to [help@rinvex.com](help@rinvex.com). All security vulnerabilities will be promptly contacted.


## About Rinvex

Rinvex is a software solutions startup, specialized in integrated enterprise solutions for SMEs established in Alexandria, Egypt since June 2016. We believe that our drive The Value, The Reach, and The Impact is what differentiates us and unleash the endless possibilities of our philosophy through the power of software. We like to call it Innovation At The Speed Of Life. That’s how we do our share of advancing humanity.


## License

This software is released under [The MIT License (MIT)](LICENSE).

(c) 2016-2020 Rinvex LLC, Some rights reserved.
