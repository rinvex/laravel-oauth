# Rinvex OAuth

**Rinvex OAuth** is an OAuth2 server and API authentication package that is simple and enjoyable to use.

[![Packagist](https://img.shields.io/packagist/v/rinvex/laravel-oauth.svg?label=Packagist&style=flat-square)](https://packagist.org/packages/rinvex/laravel-oauth)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/rinvex/laravel-oauth.svg?label=Scrutinizer&style=flat-square)](https://scrutinizer-ci.com/g/rinvex/laravel-oauth/)
[![Travis](https://img.shields.io/travis/rinvex/laravel-oauth.svg?label=TravisCI&style=flat-square)](https://travis-ci.org/rinvex/laravel-oauth)
[![StyleCI](https://styleci.io/repos/98953486/shield)](https://styleci.io/repos/98953486)
[![License](https://img.shields.io/packagist/l/rinvex/laravel-oauth.svg?label=License&style=flat-square)](https://github.com/rinvex/laravel-oauth/blob/develop/LICENSE)


## Usage

- [Introduction](#introduction)
    - [Rinvex OAuth Or Laravel Passport?](#rinvex-oauth-or-laravel-passport)
- [Installation](#installation)
    - [Deploying Rinvex OAuth](#deploying-rinvex-oauth)
    - [Migration Customization](#migration-customization)
- [Configuration](#configuration)
    - [Client Secret Hashing](#client-secret-hashing)
    - [Token Lifetimes](#token-lifetimes)
    - [Overriding Default Models](#overriding-default-models)
- [Issuing Access Tokens](#issuing-access-tokens)
    - [Managing Clients](#managing-clients)
    - [Requesting Tokens](#requesting-tokens)
    - [Refreshing Tokens](#refreshing-tokens)
    - [Revoking Tokens](#revoking-tokens)
    - [Purging Tokens](#purging-tokens)
- [Authorization Code Grant with PKCE](#code-grant-pkce)
    - [Creating The Client](#creating-a-auth-pkce-grant-client)
    - [Requesting Tokens](#requesting-auth-pkce-grant-tokens)
- [Password Grant Tokens](#password-grant-tokens)
    - [Creating A Password Grant Client](#creating-a-password-grant-client)
    - [Requesting Tokens](#requesting-password-grant-tokens)
    - [Requesting All Scopes](#requesting-all-scopes)
    - [Customizing The User Type](#customizing-the-user-type)
    - [Customizing The Username Field](#customizing-the-username-field)
    - [Customizing The Password Validation](#customizing-the-password-validation)
- [Implicit Grant Tokens](#implicit-grant-tokens)
- [Client Credentials Grant Tokens](#client-credentials-grant-tokens)
- [Personal Access Tokens](#personal-access-tokens)
    - [Creating A Personal Access Client](#creating-a-personal-access-client)
    - [Managing Personal Access Tokens](#managing-personal-access-tokens)
- [Protecting Routes](#protecting-routes)
    - [Via Middleware](#via-middleware)
    - [Passing The Access Token](#passing-the-access-token)
- [Token Scopes](#token-scopes)
    - [Defining Scopes](#defining-scopes)
    - [Default Scope](#default-scope)
    - [Assigning Scopes To Tokens](#assigning-scopes-to-tokens)
    - [Checking Scopes](#checking-scopes)
- [Consuming Your API With JavaScript](#consuming-your-api-with-javascript)

<a name="introduction"></a>
## Introduction

**Rinvex OAuth** provides a full OAuth2 server implementation for your Laravel application in a matter of minutes. **Rinvex OAuth** was inspired by and based on a lightweight modified version of [Laravel Passport](https://laravel.com/docs/master/passport) v10.3.1, which is built on top of the [League OAuth2 server](https://github.com/thephpleague/oauth2-server) that is maintained by Andy Millington and Simon Hamp.

> **Note:** This documentation assumes you are already familiar with OAuth2. If you do not know anything about OAuth2, consider familiarizing yourself with the general [terminology](https://oauth2.thephpleague.com/terminology/) and features of OAuth2 before continuing.

<a name="rinvex-oauth-or-laravel-passport"></a>
### Rinvex OAuth Or Laravel Passport?

Before getting started, you may wish to determine if your application would be better served by **Rinvex OAuth** or [Laravel Passport](https://laravel.com/docs/master/passport). The short answer is: use "Laravel Passport"! **Rinvex OAuth** is a lightweight modified version of Laravel Passport, that's simplified to fit our [Rinvex Cortex](https://github.com/rinvex/cortex) projects, so the fact that you are asking what's the difference between both, and which one to choose is enough for you to go with [Laravel Passport](https://laravel.com/docs/master/passport) without a doubt.

However, if you are attempting to use or build [Rinvex Cortex](https://github.com/rinvex/cortex) based applications, you should use **Rinvex OAuth**. **Rinvex OAuth** is required and installed by default when building APIs for **Rinvex Cortex**.

Installing **Rinvex OAuth** does NOT require Laravel Passport. It is in fact a complete lightweight replacement.

<a name="installation"></a>
## Installation

1. To get started, install **Rinvex OAuth** via the Composer package manager:
    ```shell
    composer require rinvex/laravel-oauth
    ```

2. **Rinvex OAuth** registers its own database migration directory, so you should migrate your database after installing the package. The **Rinvex OAuth** migrations will create the tables your application needs to store OAuth2 clients and access tokens:
    ```shell
    php artisan rinvex:migrate:oauth
    ```

3. Add the `Rinvex\Oauth\Traits\HasApiTokens` trait to your `App\Models\User` model. This trait will provide a few helper methods to your model which allow you to inspect the authenticated user's token and scopes:
    ```php
    namespace App\Models;

    use Rinvex\Oauth\Traits\HasApiTokens;
    use Illuminate\Foundation\Auth\User as Authenticatable;

    class User extends Authenticatable
    {
        use HasApiTokens;
    }
    ```

4. Finally, in your application's `config/auth.php` configuration file, you should set the `driver` option of the `api` authentication guard to `oauth`. This will instruct your application to use **Rinvex OAuth**'s `TokenGuard` when authenticating incoming API requests:
    ```php
    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
    
        'api' => [
            'driver' => 'oauth',
            'provider' => 'users',
        ],
    ],
    ```


<a name="deploying-rinvex-oauth"></a>
### Deploying Rinvex OAuth

When deploying **Rinvex OAuth** to your application's servers for the first time, you will likely need to run the `rinvex:oauth:keys` command. This command generates the encryption keys **Rinvex OAuth** needs in order to generate access tokens. The generated keys are not typically kept in source control:
```shell
php artisan rinvex:oauth:keys
```

If necessary, you may define the path where **Rinvex OAuth**'s keys should be loaded from. You may use the `rinvex.oauth.key_path` config option to accomplish this. Typically, this config option is null and the encryption keys are storged in `storage_path` by default.

<a name="loading-keys-from-the-environment"></a>
#### Loading Keys From The Environment

Alternatively, you may publish **Rinvex OAuth**'s configuration file using the `rinvex:publish:oauth` Artisan command:
```shell
php artisan rinvex:publish:oauth --resource=config
```

After the configuration file has been published, you may load your application's encryption keys by defining them as environment variables:
```shell
OAUTH_KEY_PATH="./storage/keys"

OAUTH_PRIVATE_KEY="-----BEGIN RSA PRIVATE KEY-----
<private key here>
-----END RSA PRIVATE KEY-----"

OAUTH_PUBLIC_KEY="-----BEGIN PUBLIC KEY-----
<public key here>
-----END PUBLIC KEY-----"
```

<a name="migration-customization"></a>
### Migration Customization

If you are not going to use **Rinvex OAuth**'s default migrations, you should export the configuration file using this command `php artisan rinvex:publish:oauth --resource=migrations`, and disable migrations autoload from confirg `rinvex.oauth.autoload_migrations` to `false`.


<a name="configuration"></a>
## Configuration

<a name="client-secret-hashing"></a>
### Client Secret Hashing

All of your client secrets are hashed and will only be displayable to the user immediately after they are created. Since the plain-text client secret value is never stored in the database, it is not possible to recover the secret's value if it is lost.

<a name="token-lifetimes"></a>
### Token Lifetimes

By default, **Rinvex OAuth** issues long-lived access tokens that expire after one year. If you would like to configure a longer / shorter token lifetime, you change default using config options. See for example `rinvex.oauth.grants.Password.expire_in`, you can configure other grants expiration in the same way.

> **Note:** The `expires_at` columns on **Rinvex OAuth**'s database tables are read-only and for display purposes only. When issuing tokens, **Rinvex OAuth** stores the expiration information within the signed and encrypted tokens. If you need to invalidate a token you should [revoke it](#revoking-tokens).

<a name="overriding-default-models"></a>
### Overriding Default Models

You are free to extend the models used internally by **Rinvex OAuth** by defining your own model and extending the corresponding **Rinvex OAuth** model:
```php
use Rinvex\Oauth\Models\Client as BaseClient;

class Client extends BaseClient
{
    // ...
}
```

After defining your model, you may instruct **Rinvex OAuth** to use your custom model via config options `rinvex.oauth.models`:
```php
'models' => [
    'client' => \Rinvex\Oauth\Models\Client::class,
    'auth_code' => \Rinvex\Oauth\Models\AuthCode::class,
    'access_token' => \Rinvex\Oauth\Models\AccessToken::class,
    'refresh_token' => \Rinvex\Oauth\Models\RefreshToken::class,
],
```

<a name="issuing-access-tokens"></a>
## Issuing Access Tokens

Using OAuth2 via authorization codes is how most developers are familiar with OAuth2. When using authorization codes, a client application will redirect a user to your server where they will either approve or deny the request to issue an access token to the client.

<a name="managing-clients"></a>
### Managing Clients

First, developers building applications that need to interact with your application's API will need to register their application with yours by creating a "client". Typically, this consists of providing the name of their application and a URL that your application can redirect to after users approve their request for authorization.

<a name="the-rinvex-oauth-client-command"></a>
#### The `rinvex:oauth:client` Command

The simplest way to create a client is using the `rinvex:oauth:client` Artisan command. This command may be used to create your own clients for testing your OAuth2 functionality. When you run the `client` command, **Rinvex OAuth** will prompt you for more information about your client and will provide you with a client ID and secret:
```shell
php artisan rinvex:oauth:client
```

**Redirect URLs**

If you would like to allow multiple redirect URLs for your client, you may specify them using a comma-delimited list when prompted for the URL by the `rinvex:oauth:client` command. Any URLs which contain commas should be URL encoded:
```shell
http://third-party-client-app.com/oauth/callback,http://fourth-party-client-app.com/oauth/callback
```

<a name="clients-frontend-interface"></a>
#### Dashboard Interface

Since your application's users will not be able to utilize the `client` command, **Rinvex OAuth** has a companion wrapping module [**Cortex OAuth**](https://github.com/rinvex/cortex-oauth) that provides complete backend and frontend dashboards that you may use to create clients. This saves you the trouble of having to manually code controllers for creating, updating, and deleting clients.

Check [**Cortex OAuth**](https://github.com/rinvex/cortex-oauth) documentation for full details on the dashboard endpoints for managing clients. The dashboard interface is guarded by the `web` and `auth` middleware; therefore, it may only be called from your own application by an authenticated user.

<a name="requesting-tokens"></a>
### Requesting Tokens

<a name="requesting-tokens-redirecting-for-authorization"></a>
#### Redirecting For Authorization

Once a client has been created, developers may use their client ID and secret to request an authorization code and access token from your application. First, the consuming application should make a redirect request to your application's `/oauth/authorize` route like so:
```php
use Illuminate\Http\Request;
use Illuminate\Support\Str;

Route::middleware(['web'])->get('oauth/redirect', function (Request $request) {
    $request->session()->put('state', $state = Str::random(40));
    $query = http_build_query([
        'client_id' => 'client-id',
        'redirect_uri' => 'http://third-party-client-app.com/callback',
        'response_type' => 'code',
        'scope' => 'scope-id-1 scope-id-2',
        'state' => $state,
    ]);

    return redirect('http://oauth-server-app.com/oauth/authorize?'.$query);
});
```

> **Notes:**
> - Scopes must be valid abilities, already created, and assigned to the user who is processing this authorization request.
> - Remember, if you are using [**Cortex OAuth**](https://github.com/rinvex/cortex-oauth), you do not need to manually define this route `/oauth/authorize` as it is already defined by the module.

<a name="approving-the-request"></a>
#### Approving The Request

If you're using [**Cortex OAuth**](https://github.com/rinvex/cortex-oauth), when receiving authorization requests, **Cortex OAuth** will automatically display a template to the user allowing them to approve or deny the authorization request. If they approve the request, they will be redirected back to the `redirect_uri` that was specified by the consuming application. The `redirect_uri` must match the `redirect` URL that was specified when the client was created.

If you would like to customize the authorization approval screen, you may publish **Rinvex OAuth**'s views using the `cortex:publish:oauth` Artisan command. The published views will be placed in the `resources/views/vendor/cortex/oauth` directory:
```shell
php artisan cortex:publish:oauth --resource=views
```

Sometimes you may wish to skip the authorization prompt, such as when authorizing a first-party client. You may accomplish this by [extending the `Client` model](#overriding-default-models) and defining a `skipsAuthorization` method. If `skipsAuthorization` returns `true` the client will be approved and the user will be redirected back to the `redirect_uri` immediately:
```php
use Rinvex\Oauth\Models\Client as BaseClient;

class Client extends BaseClient
{
    /**
     * Determine if the client should skip the authorization prompt.
     *
     * @return bool
     */
    public function skipsAuthorization()
    {
        return $this->firstParty();
    }
}
```

<a name="requesting-tokens-converting-authorization-codes-to-access-tokens"></a>
#### Converting Authorization Codes To Access Tokens

If the user approves the authorization request, they will be redirected back to the consuming application. The consumer should first verify the `state` parameter against the value that was stored prior to the redirect. If the state parameter matches then the consumer should issue a `POST` request to your application to request an access token. The request should include the authorization code that was issued by your application when the user approved the authorization request:
```php
use Illuminate\Http\Request;
use InvalidArgumentException;
use Illuminate\Support\Facades\Http;

Route::middleware(['web'])->get('oauth/callback', function (Request $request) {
    $state = $request->session()->pull('state');

    throw_unless(
        strlen($state) > 0 && $state === $request->state,
        InvalidArgumentException::class
    );

    $response = Http::asForm()->post('http://oauth-server-app.com/oauth/token', [
        'grant_type' => 'authorization_code',
        'client_id' => 'client-id',
        'client_secret' => 'client-secret',
        'redirect_uri' => 'http://third-party-client-app.com/oauth/callback',
        'code' => $request->code,
    ]);

    return $response->json();
});
```

This `/oauth/token` route will return a JSON response containing `access_token`, `refresh_token`, and `expires_in` attributes. The `expires_in` attribute contains the number of seconds until the access token expires.

> **Note:** if you are using [**Cortex OAuth**](https://github.com/rinvex/cortex-oauth), you do not need to manually define the `/oauth/token` route as it is defined for you by the module, like `/oauth/authorize`. There is no need to manually define this route.

<a name="refreshing-tokens"></a>
### Refreshing Tokens

If your application issues short-lived access tokens, users will need to refresh their access tokens via the refresh token that was provided to them when the access token was issued:
```php
use Illuminate\Support\Facades\Http;

$response = Http::asForm()->post('http://oauth-server-app.com/oauth/token', [
    'grant_type' => 'refresh_token',
    'refresh_token' => 'the-refresh-token',
    'client_id' => 'client-id',
    'client_secret' => 'client-secret',
    'scope' => 'scope-id-1 scope-id-2',
]);

return $response->json();
```

This `/oauth/token` route will return a JSON response containing `access_token`, `refresh_token`, and `expires_in` attributes. The `expires_in` attribute contains the number of seconds until the access token expires.

<a name="revoking-tokens"></a>
### Revoking Tokens

You may revoke access token by using the `revoke` method on the `Rinvex\Oauth\Models\AccessToken`.
```php
app('rinvex.oauth.access_token')->where('identifier', $tokenId)->get()->revoke();
```

Alternatively, you can achieve the same result directly by using the `revokeAccessToken` method on the `Rinvex\Oauth\Repositories\AccessTokenRepository`.
```php
use Rinvex\Oauth\Repositories\AccessTokenRepository;
use Rinvex\Oauth\Repositories\RefreshTokenRepository;

// Revoke an access token...
$accessTokenRepository = app(AccessTokenRepository::class);
$accessTokenRepository->revokeAccessToken($tokenId);
```

> **Note:** Revoking access token, will revoke all associated refresh tokens as well.

You may revoke a specific refresh token by using the `revoke` method on the `Rinvex\Oauth\Models\RefreshToken`.
```php
app('rinvex.oauth.refresh_token')->where('identifier', $tokenId)->get()->revoke();
```

<a name="purging-tokens"></a>
### Purging Tokens

When tokens have been revoked or expired, you might want to purge them from the database. **Rinvex OAuth**'s included `rinvex:oauth:purge` Artisan command can do this for you:
```shell
# Purge revoked and expired tokens and auth codes...
php artisan rinvex:oauth:purge

# Only purge revoked tokens and auth codes...
php artisan rinvex:oauth:purge --revoked

# Only purge expired tokens and auth codes...
php artisan rinvex:oauth:purge --expired
```

You may also configure a [scheduled job](https://laravel.com/docs/master/scheduling) in your application's `App\Console\Kernel` class to automatically prune your tokens on a schedule:
```php
/**
 * Define the application's command schedule.
 *
 * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
 * @return void
 */
protected function schedule(Schedule $schedule)
{
    $schedule->command('rinvex:oauth:purge')->hourly();
}
```

<a name="code-grant-pkce"></a>
## Authorization Code Grant with PKCE

The Authorization Code grant with "Proof Key for Code Exchange" (PKCE) is a secure way to authenticate single page applications or native applications to access your API. This grant should be used when you can't guarantee that the client secret will be stored confidentially or in order to mitigate the threat of having the authorization code intercepted by an attacker. A combination of a "code verifier" and a "code challenge" replaces the client secret when exchanging the authorization code for an access token.

<a name="creating-a-auth-pkce-grant-client"></a>
### Creating The Client

Before your application can issue tokens via the authorization code grant with PKCE, you will need to create a PKCE-enabled client. You may do this using the `rinvex:oauth:client` Artisan command with the `--public` option:
```shell
php artisan rinvex:oauth:client --public
```

<a name="requesting-auth-pkce-grant-tokens"></a>
### Requesting Tokens

<a name="code-verifier-code-challenge"></a>
#### Code Verifier & Code Challenge

As this authorization grant does not provide a client secret, developers will need to generate a combination of a code verifier and a code challenge in order to request a token.

The code verifier should be a random string of between 43 and 128 characters containing letters, numbers, and  `"-"`, `"."`, `"_"`, `"~"` characters, as defined in the [RFC 7636 specification](https://tools.ietf.org/html/rfc7636).

The code challenge should be a Base64 encoded string with URL and filename-safe characters. The trailing `'='` characters should be removed and no line breaks, whitespace, or other additional characters should be present.
```php
$encoded = base64_encode(hash('sha256', $code_verifier, true));

$codeChallenge = strtr(rtrim($encoded, '='), '+/', '-_');
```

<a name="code-grant-pkce-redirecting-for-authorization"></a>
#### Redirecting For Authorization

Once a client has been created, you may use the client ID and the generated code verifier and code challenge to request an authorization code and access token from your application. First, the consuming application should make a redirect request to your application's `/oauth/authorize` route:
```php
use Illuminate\Support\Str;
use Illuminate\Http\Request;

Route::get('oauth/redirect', function (Request $request) {
    $request->session()->put('state', $state = Str::random(40));

    $request->session()->put(
        'code_verifier', $code_verifier = Str::random(128)
    );

    $codeChallenge = strtr(rtrim(
        base64_encode(hash('sha256', $code_verifier, true))
    , '='), '+/', '-_');

    $query = http_build_query([
        'client_id' => 'client-id',
        'redirect_uri' => 'http://third-party-app.com/callback',
        'response_type' => 'code',
        'scope' => 'scope-id-1 scope-id-2',
        'state' => $state,
        'code_challenge' => $codeChallenge,
        'code_challenge_method' => 'S256',
    ]);

    return redirect('http://oauth-server-app.com/oauth/authorize?'.$query);
});
```

<a name="code-grant-pkce-converting-authorization-codes-to-access-tokens"></a>
#### Converting Authorization Codes To Access Tokens

If the user approves the authorization request, they will be redirected back to the consuming application. The consumer should verify the `state` parameter against the value that was stored prior to the redirect, as in the standard Authorization Code Grant.

If the state parameter matches, the consumer should issue a `POST` request to your application to request an access token. The request should include the authorization code that was issued by your application when the user approved the authorization request along with the originally generated code verifier:
```php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

Route::get('callback', function (Request $request) {
    $state = $request->session()->pull('state');

    $codeVerifier = $request->session()->pull('code_verifier');

    throw_unless(
        strlen($state) > 0 && $state === $request->state,
        InvalidArgumentException::class
    );

    $response = Http::asForm()->post('http://oauth-server-app.com/oauth/token', [
        'grant_type' => 'authorization_code',
        'client_id' => 'client-id',
        'redirect_uri' => 'http://third-party-app.com/callback',
        'code_verifier' => $codeVerifier,
        'code' => $request->code,
    ]);

    return $response->json();
});
```

<a name="password-grant-tokens"></a>
## Password Grant Tokens

The OAuth2 password grant allows your other first-party clients, such as a mobile application, to obtain an access token using an email address / username and password. This allows you to issue access tokens securely to your first-party clients without requiring your users to go through the entire OAuth2 authorization code redirect flow.

<a name="creating-a-password-grant-client"></a>
### Creating A Password Grant Client

Before your application can issue tokens via the password grant, you will need to create a password grant client. You may do this using the `rinvex:oauth:client` Artisan command with the `--password` option. **If you are using [**Cortex OAuth**](https://github.com/rinvex/cortex-oauth) and you have already run the `cortex:install:oauth` command, you do not need to run this command:**
```shell
php artisan rinvex:oauth:client --password
```

<a name="requesting-password-grant-tokens"></a>
### Requesting Tokens

Once you have created a password grant client, you may request an access token by issuing a `POST` request to the `/oauth/token` route with the user's email address and password. Remember, If you are using [**Cortex OAuth**](https://github.com/rinvex/cortex-oauth), this route is already registered for you, so there is no need to define it manually. If the request is successful, you will receive an `access_token` and `refresh_token` in the JSON response from the server:
```php
use Illuminate\Support\Facades\Http;

$response = Http::asForm()->post('http://oauth-server-app.com/oauth/token', [
    'grant_type' => 'password',
    'client_id' => 'client-id',
    'client_secret' => 'client-secret',
    'username' => 'my@email.com',
    'password' => 'my-password',
    'scope' => 'scope-id-1 scope-id-2',
]);

return $response->json();
```

> **Note: Remember, access tokens are long-lived by default. However, you are free to [configure your maximum access token lifetime](#configuration) if needed.

<a name="requesting-all-scopes"></a>
### Requesting All Scopes

When using the password grant or client credentials grant, you may wish to authorize the token for all of the scopes supported by your application. You can do this by requesting the `*` scope. If you request the `*` scope, the `can` method on the token instance will always return `true`. This scope may only be assigned to a token that is issued using the `password` or `client_credentials` grant:
```php
use Illuminate\Support\Facades\Http;

$response = Http::asForm()->post('http://oauth-server-app.com/oauth/token', [
    'grant_type' => 'password',
    'client_id' => 'client-id',
    'client_secret' => 'client-secret',
    'username' => 'my@email.com',
    'password' => 'my-password',
    'scope' => '*',
]);
```

<a name="customizing-the-user-type"></a>
### Customizing The User Type

If your application uses more than one [authentication guards](https://laravel.com/docs/master/authentication#adding-custom-guards), you may specify which guard the password grant client uses by providing a `--user_type` option when creating the client via the `artisan rinvex:oauth:client --password` command. The given guard name should match a valid guard defined in your application's `config/auth.php` configuration file. You can then [protect your route using middleware](#via-middleware) to ensure that only users from the guard is authorized.

<a name="customizing-the-username-field"></a>
### Customizing The Username Field

When authenticating using the password grant, **Rinvex OAuth** will use the `email` attribute of your authenticatable model as the "username". However, you may customize this behavior by defining a `findForOAuth` method on your model:
```php
namespace App\Models;

use Rinvex\Oauth\Traits\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    /**
     * Find the user instance for the given username.
     *
     * @param  string  $username
     * @return \App\Models\User
     */
    public function findForOAuth($username)
    {
        return $this->where('username', $username)->first();
    }
}
```

<a name="customizing-the-password-validation"></a>
### Customizing The Password Validation

When authenticating using the password grant, **Rinvex OAuth** will use the `password` attribute of your model to validate the given password. If your model does not have a `password` attribute or you wish to customize the password validation logic, you can define a `validateForOAuthPasswordGrant` method on your model:
```php
namespace App\Models;

use Illuminate\Support\Facades\Hash;
use Rinvex\Oauth\Traits\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    /**
     * Validate the password of the user for the OAuth password grant.
     *
     * @param  string  $password
     * @return bool
     */
    public function validateForOAuthPasswordGrant($password)
    {
        return Hash::check($password, $this->password);
    }
}
```

<a name="implicit-grant-tokens"></a>
## Implicit Grant Tokens

The implicit grant is similar to the authorization code grant; however, the token is returned to the client without exchanging an authorization code. This grant is most commonly used for JavaScript or mobile applications where the client credentials can't be securely stored. You can enable the grant by publishing config file using this command `php artisan rinvex:publish:oauth --resource=config`, then enabling that grant option. It is disabled by default, and not recommended to use since other grants are more secure.
```php
'grants' => [
    'Password' => ['enabled' => true, 'expire_in' => new DateInterval('P1Y')],
    'Implicit' => ['enabled' => false, 'expire_in' => new DateInterval('P1Y')],
    'AuthCode' => ['enabled' => true, 'expire_in' => new DateInterval('P1Y')],
    'RefreshToken' => ['enabled' => true, 'expire_in' => new DateInterval('P1Y')],
    'PersonalAccess' => ['enabled' => true, 'expire_in' => new DateInterval('P1Y')],
    'ClientCredentials' => ['enabled' => true, 'expire_in' => new DateInterval('P1Y')],
],
```

Once the grant has been enabled, developers may use their client ID to request an access token from your application. The consuming application should make a redirect request to your application's `/oauth/authorize` route like so:
```php
use Illuminate\Http\Request;

Route::get('redirect', function (Request $request) {
    $request->session()->put('state', $state = Str::random(40));

    $query = http_build_query([
        'client_id' => 'client-id',
        'redirect_uri' => 'http://third-party-app.com/callback',
        'response_type' => 'token',
        'scope' => 'scope-id-1 scope-id-2',
        'state' => $state,
    ]);

    return redirect('http://oauth-server-app.com/oauth/authorize?'.$query);
});
```

> **Note:** Remember, if you are using [**Cortex OAuth**](https://github.com/rinvex/cortex-oauth), you do not need to manually define this route `/oauth/authorize` as it is already defined by the module.

<a name="client-credentials-grant-tokens"></a>
## Client Credentials Grant Tokens

The client credentials grant is suitable for machine-to-machine authentication. For example, you might use this grant in a scheduled job which is performing maintenance tasks over an API.

Before your application can issue tokens via the client credentials grant, you will need to create a client credentials grant client. You may do this using the `--client_credentials` option of the `rinvex:oauth:client` Artisan command:
```shell
php artisan rinvex:oauth:client --client_credentials
```

Next, to use this grant type, you need to add the `CheckClientCredentials` middleware to the `$routeMiddleware` property of your `app/Http/Kernel.php` file:
```php
use Rinvex\Oauth\Http\Middleware\CheckClientCredentials;

protected $routeMiddleware = [
    'client' => CheckClientCredentials::class,
];
```

> **Note:** Remember, if you are using [**Cortex OAuth**](https://github.com/rinvex/cortex-oauth), you do not need to manually register this middleware `CheckClientCredentials` as it is already registered for you by the module.

Then, attach the middleware to a route:
```php
Route::get('orders', function (Request $request) {
    ...
})->middleware('client');
```

To restrict access to the route to specific scopes, you may provide a comma-delimited list of the required scopes when attaching the `client` middleware to the route:
```php
Route::get('/orders', function (Request $request) {
    // ...
})->middleware('client:scope-id-1,scope-id-2');
```

<a name="retrieving-tokens"></a>
### Retrieving Tokens

To retrieve a token using this grant type, make a request to the `/oauth/token` endpoint:
```php
use Illuminate\Support\Facades\Http;

$response = Http::asForm()->post('http://oauth-server-app.com/oauth/token', [
    'grant_type' => 'client_credentials',
    'client_id' => 'client-id',
    'client_secret' => 'client-secret',
    'scope' => 'your-scope',
]);

return $response->json()['access_token'];
```

> **Note:** Remember, if you are using [**Cortex OAuth**](https://github.com/rinvex/cortex-oauth), you do not need to manually define this route `/oauth/token` as it is already registered for you by the module.

<a name="personal-access-tokens"></a>
## Personal Access Tokens

Sometimes, your users may want to issue access tokens to themselves without going through the typical authorization code redirect flow. Allowing users to issue tokens to themselves via your application's UI can be useful for allowing users to experiment with your API or may serve as a simpler approach to issuing access tokens in general.

<a name="creating-a-personal-access-client"></a>
### Creating A Personal Access Client

Before your application can issue personal access tokens, you will need to create a personal access client. You may do this by executing the `rinvex:oauth:client` Artisan command with the `--personal_access` option. **If you are using [**Cortex OAuth**](https://github.com/rinvex/cortex-oauth) and you have already run the `cortex:install:oauth` command, you do not need to run this command:**
```shell
php artisan rinvex:oauth:client --personal_access
```

After creating your personal access client, place the client's ID and plain-text secret value in your application's `.env` file:
```shell
OAUTH_PERSONAL_ACCESS_CLIENT_ID="client-id-value"
OAUTH_PERSONAL_ACCESS_CLIENT_SECRET="unhashed-client-secret-value"
```

You can also configure this through the config file, by publishing it first using this command `php artisan rinvex:publish:oauth --resource=config`, then updating the relevant option:
```php
'personal_access_client' => [
    'id' => env('OAUTH_PERSONAL_ACCESS_CLIENT_ID'),
    'secret' => env('OAUTH_PERSONAL_ACCESS_CLIENT_SECRET'),
],
```

<a name="managing-personal-access-tokens"></a>
### Managing Personal Access Tokens

Once you have created a personal access client, you may issue tokens for a given user using the `createToken` method on the `App\Models\User` model instance. The `createToken` method accepts the name of the token as its first argument and an optional array of [scopes](#token-scopes) as its second argument:
```php
use App\Models\User;

$user = User::find(1);

// Creating a token without scopes...
$token = $user->createToken('Token Name')->accessToken;

// Creating a token with scopes...
$token = $user->createToken('My Token', ['scope-id-1', 'scope-id-2'])->accessToken;
```

<a name="protecting-routes"></a>
## Protecting Routes

<a name="via-middleware"></a>
### Via Middleware

**Rinvex OAuth** includes an [authentication guard](https://laravel.com/docs/master/authentication#adding-custom-guards) that will validate access tokens on incoming requests. Once you have configured the `api` guard to use the `oauth` driver, you only need to specify the `auth:api` middleware on any routes that should require a valid access token:
```php
Route::get('user', function () {
    // ...
})->middleware('auth:api');
```

<a name="multiple-authentication-guards"></a>
#### Multiple Authentication Guards

If your application authenticates different types of users that perhaps use entirely different Eloquent models, you will likely need to define a guard configuration for each user provider type in your application. This allows you to protect requests intended for specific user providers. For example, given the following guard configuration the `config/auth.php` configuration file:
```php
'api:member' => [
    'driver' => 'oauth',
    'provider' => 'members',
],

'api:admin' => [
    'driver' => 'oauth',
    'provider' => 'admins',
],
```

The following route will utilize the `api:member` guard, which uses the `members` user provider, to authenticate incoming requests:
```php
Route::get('customer', function () {
    //
})->middleware('auth:api:member');
```

> **Note:** For more information on using multiple user providers with **Rinvex OAuth**, please consult the [password grant documentation](#customizing-the-user-type).

<a name="passing-the-access-token"></a>
### Passing The Access Token

When calling routes that are protected by **Rinvex OAuth**, your application's API consumers should specify their access token as a `Bearer` token in the `Authorization` header of their request. For example, when using the Guzzle HTTP library:
```php
use Illuminate\Support\Facades\Http;

$response = Http::withHeaders([
    'Accept' => 'application/json',
    'Authorization' => 'Bearer '.$accessToken,
])->get('https://oauth-server-app.com/api/user');

return $response->json();
```

<a name="token-scopes"></a>
## Token Scopes

Scopes allow your API clients to request a specific set of permissions when requesting authorization to access an account. For example, if you are building an e-commerce application, not all API consumers will need the ability to place orders. Instead, you may allow the consumers to only request authorization to access order shipment statuses. In other words, scopes allow your application's users to limit the actions a third-party application can perform on their behalf.

<a name="defining-scopes"></a>
### Defining Scopes

Scopes are using the underlying abilities defined by [**silber/bouncer**](https://github.com/JosephSilber/bouncer) package, which means each scope id must match a valid ability id in your system, and this is where **Rinvex OAuth** majorly differs than **Laravel Passport**. Please check [creating abilities documentation](https://github.com/JosephSilber/bouncer#creating-roles-and-abilities) first before utilizing the scopes feature of **Rinvex OAuth**.

That way, you've one centralized place to manage all access permissions to all resources. 

> **Note:** Remember that the user issuing a new access token, must have the same API request scopes, otherwise access will be prohibited

<a name="default-scope"></a>
### Default Scope

If a client does not request any specific scopes, you may configure your **Rinvex OAuth** server to attach default scope(s) to the token in your config file. First you'll need to publish config file:
```shell
php artisan rinvex:publish:oauth --resource=config
```

Then you can update the config option:
```php
'default_scope' => null,
```

<a name="assigning-scopes-to-tokens"></a>
### Assigning Scopes To Tokens

<a name="when-requesting-authorization-codes"></a>
#### When Requesting Authorization Codes

When requesting an access token using the authorization code grant, consumers should specify their desired scopes as the `scope` query string parameter. The `scope` parameter should be a space-delimited list of scopes:
```php
Route::get('redirect', function () {
    $query = http_build_query([
        'client_id' => 'client-id',
        'redirect_uri' => 'http://third-party-client-app.com/oauth/callback',
        'response_type' => 'code',
        'scope' => 'scope-id-1 scope-id-2',
    ]);

    return redirect('http://oauth-server-app.com/oauth/authorize?'.$query);
});
```

<a name="when-issuing-personal-access-tokens"></a>
#### When Issuing Personal Access Tokens

If you are issuing personal access tokens using the `App\Models\User` model's `createToken` method, you may pass the array of desired scopes as the second argument to the method:
```php
$token = $user->createToken('My Token', ['scope-id-1', 'scope-id-2'])->accessToken;
```

<a name="checking-scopes"></a>
### Checking Scopes

**Rinvex OAuth** includes two middleware that may be used to verify that an incoming request is authenticated with a token that has been granted a given scope. To get started, add the following middleware to the `$routeMiddleware` property of your `app/Http/Kernel.php` file:
```php
'scopes' => \Rinvex\Oauth\Http\Middleware\CheckScopes::class,
'scope' => \Rinvex\Oauth\Http\Middleware\CheckForAnyScope::class,
```

> **Note:** Remember, if you are using [**Cortex OAuth**](https://github.com/rinvex/cortex-oauth), you do not need to manually register these middleware `CheckScopes` and `CheckForAnyScope` as they're already registered for you by the module.

<a name="check-for-all-scopes"></a>
#### Check For All Scopes

The `scopes` middleware may be assigned to a route to verify that the incoming request's access token has all of the listed scopes:
```php
Route::get('orders', function () {
    // Access token has both "scope-id-2" and "scope-id-1" scopes...
})->middleware(['auth:api', 'scopes:scope-id-2,scope-id-1']);
```

<a name="check-for-any-scopes"></a>
#### Check For Any Scopes

The `scope` middleware may be assigned to a route to verify that the incoming request's access token has *at least one* of the listed scopes:
```php
Route::get('orders', function () {
    // Access token has either "scope-id-2" or "scope-id-1" scope...
})->middleware(['auth:api', 'scope:scope-id-2,scope-id-1']);
```

<a name="checking-scopes-on-a-token-instance"></a>
#### Checking Scopes On A Token Instance

Once an access token authenticated request has entered your application, you may still check if the token has a given scope as follows:
```php
use Illuminate\Http\Request;

Route::get('orders', function (Request $request) {
    $scope = 'scope-id-1';

    if ($request->user()->token()->abilities->map->getRouteKey()->contains($scope)) {
        //
    }
});
```

<a name="consuming-your-api-with-javascript"></a>
## Consuming Your API With JavaScript

When building an API, it can be extremely useful to be able to consume your own API from your JavaScript application. This approach to API development allows your own application to consume the same API that you are sharing with the world. The same API may be consumed by your web application, mobile applications, third-party applications, and any SDKs that you may publish on various package managers.

Typically, if you want to consume your API from your JavaScript application, you would need to manually send an access token to the application and pass it with each request to your application. However, **Rinvex OAuth** includes a middleware that can handle this for you. All you need to do is add the `CreateFreshApiToken` middleware to your `web` middleware group in your `app/Http/Kernel.php` file:
```php
'web' => [
    // Other middleware...
    \Rinvex\Oauth\Http\Middleware\CreateFreshApiToken::class,
],
```

> **Note:** You should ensure that the `CreateFreshApiToken` middleware is the last middleware listed in your middleware stack.

This middleware will attach a `laravel_token` cookie to your outgoing responses. This cookie contains an encrypted JWT that **Rinvex OAuth** will use to authenticate API requests from your JavaScript application. The JWT has a lifetime equal to your `session.lifetime` configuration value. Now, since the browser will automatically send the cookie with all subsequent requests, you may make requests to your application's API without explicitly passing an access token:
```javascript
axios.get('api/user')
    .then(response => {
        console.log(response.data);
    });
```

<a name="customizing-the-cookie-name"></a>
### Customizing The Cookie Name

If needed, you can customize the `laravel_token` cookie's name using the corresponding config option. First you'll need to publish the config file using this command `php artisan rinvex:publish:oauth --resource=config`, then you can modify as follows:
```php
'default_scope' => null,
```

<a name="csrf-protection"></a>
### CSRF Protection

When using this method of authentication, you will need to ensure a valid CSRF token header is included in your requests. The default Laravel JavaScript scaffolding includes an Axios instance, which will automatically use the encrypted `XSRF-TOKEN` cookie value to send a `X-XSRF-TOKEN` header on same-origin requests.

> **Note:** If you choose to send the `X-CSRF-TOKEN` header instead of `X-XSRF-TOKEN`, you will need to use the unencrypted token provided by `csrf_token()`.


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

(c) 2016-2022 Rinvex LLC, Some rights reserved.
