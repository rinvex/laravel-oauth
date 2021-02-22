<?php

declare(strict_types=1);

namespace Rinvex\OAuth\Providers;

use DateInterval;
use Rinvex\OAuth\OAuth;
use Illuminate\Support\Str;
use Rinvex\OAuth\Models\Client;
use Illuminate\Auth\AuthManager;
use Illuminate\Auth\RequestGuard;
use Rinvex\OAuth\Models\AuthCode;
use Illuminate\Auth\Events\Logout;
use League\OAuth2\Server\CryptKey;
use Rinvex\OAuth\Guards\TokenGuard;
use Rinvex\OAuth\OAuthUserProvider;
use Illuminate\Support\Facades\Auth;
use Rinvex\OAuth\Models\AccessToken;
use Illuminate\Support\Facades\Event;
use Rinvex\OAuth\Models\RefreshToken;
use Rinvex\OAuth\PersonalAccessGrant;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\ServiceProvider;
use Rinvex\Support\Traits\ConsoleTools;
use League\OAuth2\Server\ResourceServer;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\ImplicitGrant;
use League\OAuth2\Server\Grant\PasswordGrant;
use Rinvex\OAuth\Repositories\UserRepository;
use Rinvex\OAuth\Console\Commands\KeysCommand;
use Rinvex\OAuth\Repositories\ScopeRepository;
use Rinvex\OAuth\Console\Commands\PurgeCommand;
use Rinvex\OAuth\Repositories\ClientRepository;
use Rinvex\OAuth\Console\Commands\ClientCommand;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use Rinvex\OAuth\Console\Commands\MigrateCommand;
use Rinvex\OAuth\Console\Commands\PublishCommand;
use Rinvex\OAuth\Repositories\AuthCodeRepository;
use Rinvex\OAuth\Console\Commands\RollbackCommand;
use Rinvex\OAuth\Repositories\AccessTokenRepository;
use Rinvex\OAuth\Repositories\RefreshTokenRepository;
use League\OAuth2\Server\Grant\ClientCredentialsGrant;

class OAuthServiceProvider extends ServiceProvider
{
    use ConsoleTools;

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // Publish Resources
        $this->publishesConfig('rinvex/laravel-oauth');
        $this->publishesMigrations('rinvex/laravel-oauth');
        ! $this->autoloadMigrations('rinvex/laravel-oauth') || $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

        $this->deleteCookieOnLogout();

        if ($this->app->runningInConsole()) {
            $this->commands([
                KeysCommand::class,
                PurgeCommand::class,
                ClientCommand::class,
                MigrateCommand::class,
                PublishCommand::class,
                RollbackCommand::class,
            ]);
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        // Merge config
        $this->mergeConfigFrom(realpath(__DIR__.'/../../config/config.php'), 'rinvex.oauth');

        // Bind eloquent models to IoC container
        $this->registerModels([
            'rinvex.oauth.client' => Client::class,
            'rinvex.oauth.auth_code' => AuthCode::class,
            'rinvex.oauth.access_token' => AccessToken::class,
            'rinvex.oauth.refresh_token' => RefreshToken::class,
        ]);

        $this->registerAuthorizationServer();
        $this->registerClientRepository();
        $this->registerResourceServer();
        $this->registerGuard();
    }

    /**
     * Register the authorization server.
     *
     * @return void
     */
    protected function registerAuthorizationServer()
    {
        $this->app->singleton(AuthorizationServer::class, function () {
            return tap($this->makeAuthorizationServer(), function ($server) {
                $server->setDefaultScope(config('rinvex.oauth.default_scope'));

                foreach (collect(config('rinvex.oauth.grants'))->filter(fn ($args) => $args['enabled']) as $grant => $options) {
                    $makeGrantMethod = "make{$grant}Grant";

                    $server->enableGrantType(
                        $this->{$makeGrantMethod}(),
                        $options['expire_in']
                    );
                }
            });
        });
    }

    /**
     * Create and configure an instance of the personal access grant.
     *
     * @return \Rinvex\OAuth\PersonalAccessGrant
     */
    protected function makePersonalAccessGrant()
    {
        return new PersonalAccessGrant();
    }

    /**
     * Create and configure an instance of the client credentials grant.
     *
     * @return \League\OAuth2\Server\Grant\ClientCredentialsGrant
     */
    protected function makeClientCredentialsGrant()
    {
        return new ClientCredentialsGrant();
    }

    /**
     * Create and configure an instance of the Auth Code grant.
     *
     * @return \League\OAuth2\Server\Grant\AuthCodeGrant
     */
    protected function makeAuthCodeGrant()
    {
        return tap($this->buildAuthCodeGrant(), function ($grant) {
            $grant->setRefreshTokenTTL(config('rinvex.oauth.grants.AuthCode.expire_in'));
        });
    }

    /**
     * Create and configure a Refresh Token grant instance.
     *
     * @return \League\OAuth2\Server\Grant\RefreshTokenGrant
     */
    protected function makeRefreshTokenGrant()
    {
        $repository = $this->app->make(RefreshTokenRepository::class);

        return tap(new RefreshTokenGrant($repository), function ($grant) {
            $grant->setRefreshTokenTTL(config('rinvex.oauth.grants.RefreshToken.expire_in'));
        });
    }

    /**
     * Create and configure a Password grant instance.
     *
     * @return \League\OAuth2\Server\Grant\PasswordGrant
     */
    protected function makePasswordGrant()
    {
        $grant = new PasswordGrant(
            $this->app->make(UserRepository::class),
            $this->app->make(RefreshTokenRepository::class)
        );

        $grant->setRefreshTokenTTL(config('rinvex.oauth.grants.Password.expire_in'));

        return $grant;
    }

    /**
     * Create and configure an instance of the Implicit grant.
     *
     * @return \League\OAuth2\Server\Grant\ImplicitGrant
     */
    protected function makeImplicitGrant()
    {
        return new ImplicitGrant(config('rinvex.oauth.grants.Implicit.expire_in'));
    }

    /**
     * Build the Auth Code grant instance.
     *
     * @return \League\OAuth2\Server\Grant\AuthCodeGrant
     */
    protected function buildAuthCodeGrant()
    {
        return new AuthCodeGrant(
            $this->app->make(AuthCodeRepository::class),
            $this->app->make(RefreshTokenRepository::class),
            new DateInterval('PT10M')
        );
    }

    /**
     * Make the authorization service instance.
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     *
     * @return \League\OAuth2\Server\AuthorizationServer
     */
    public function makeAuthorizationServer()
    {
        return new AuthorizationServer(
            $this->app->make(ClientRepository::class),
            $this->app->make(AccessTokenRepository::class),
            $this->app->make(ScopeRepository::class),
            $this->makeCryptKey('private'),
            app('encrypter')->getKey()
        );
    }

    /**
     * Register the client repository.
     *
     * @return void
     */
    protected function registerClientRepository()
    {
        $this->app->singleton(ClientRepository::class, function () {
            return new ClientRepository();
        });
    }

    /**
     * Register the resource server.
     *
     * @return void
     */
    protected function registerResourceServer()
    {
        $this->app->singleton(ResourceServer::class, function () {
            return new ResourceServer(
                $this->app->make(AccessTokenRepository::class),
                $this->makeCryptKey('public')
            );
        });
    }

    /**
     * Create a CryptKey instance without permissions check.
     *
     * @param string $type
     *
     * @return \League\OAuth2\Server\CryptKey
     */
    protected function makeCryptKey($type)
    {
        $key = str_replace('\\n', "\n", config("rinvex.oauth.{$type}_key"));

        if (! $key) {
            $key = 'file://'.OAuth::keyPath('oauth-'.$type.'.key');
        }

        return new CryptKey($key, null, false);
    }

    /**
     * Register the token guard.
     *
     * @return void
     */
    protected function registerGuard()
    {
        Auth::resolved(function (AuthManager $auth) {
            $auth->extend('oauth', function ($app, $name, array $config) {
                return tap($this->makeGuard($config), function ($guard) {
                    $this->app->refresh('request', $guard, 'setRequest');
                });
            });
        });
    }

    /**
     * Make an instance of the token guard.
     *
     * @param array $config
     *
     * @return \Illuminate\Auth\RequestGuard
     */
    protected function makeGuard(array $config)
    {
        return new RequestGuard(function ($request) use ($config) {
            return (new TokenGuard(
                $this->app->make(ResourceServer::class),
                new OAuthUserProvider(Auth::createUserProvider($config['provider']), Str::singular($config['provider'])),
                $this->app->make('encrypter')
            ))->user($request);
        }, $this->app['request']);
    }

    /**
     * Register the cookie deletion event handler.
     *
     * @return void
     */
    protected function deleteCookieOnLogout()
    {
        Event::listen(Logout::class, function () {
            if (Request::hasCookie(config('rinvex.oauth.cookie'))) {
                Cookie::queue(Cookie::forget(config('rinvex.oauth.cookie')));
            }
        });
    }
}
