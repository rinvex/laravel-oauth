<?php

declare(strict_types=1);

namespace Rinvex\OAuth\Console\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Rinvex\OAuth\Models\Client;
use Rinvex\Support\Traits\ArtisanCanValidateAnswers;

class ClientCommand extends Command
{
    use ArtisanCanValidateAnswers;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rinvex:oauth:client
            {--client_credentials : Create a client credentials grant client}
            {--personal_access : Create a personal access client}
            {--password : Create a password grant client}
            {--name= : The name of the client}
            {--provider= : The name of the user provider}
            {--redirect_uri= : The URI to redirect to after authorization }
            {--user_id= : The user ID the client should be assigned to }
            {--public : Create a public client (Auth code grant type only) }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a client for issuing access tokens';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->option('personal_access')) {
            $this->createPersonalAccessClient();
        } elseif ($this->option('password')) {
            $this->createPasswordClient();
        } elseif ($this->option('client_credentials')) {
            $this->createClientCredentialsClient();
        } else {
            $this->createAuthorizationCodeClient();
        }
    }

    /**
     * Create a new personal access client.
     *
     * @return void
     */
    protected function createPersonalAccessClient()
    {
        $this->alert('Create Personal Access Client');

        $name = $this->option('name') ?: $this->askValid('What should we name the client?', 'name', 'required|string|strip_tags');
        $userId = $this->option('user_id') ?: $this->askValid('Which user ID should the client be assigned to?', 'user_id', 'required|integer');
        $redirect = $this->option('redirect_uri') ?: $this->askValid('Where should we redirect the request after authorization?', 'redirect_uri', 'required|string|url|max:1500', url('/auth/callback'));

        $providers = array_keys(config('auth.providers'));
        $provider = $this->option('provider') ?: $this->choice(
            'Which user provider should this client use to retrieve users?',
            $providers,
            in_array('users', $providers) ? 'users' : null
        );

        $client = app('rinvex.oauth.client')->create([
            'user_id' => $userId,
            'name' => $name,
            'secret' => Str::random(40),
            'provider' => $provider,
            'redirect' => $redirect,
            'grant_type' => 'personal_access',
        ]);

        $this->info('Personal access client created successfully.');

        $this->outputClientDetails($client);
    }

    /**
     * Create a new password grant client.
     *
     * @return void
     */
    protected function createPasswordClient()
    {
        $this->alert('Create Password Client');

        $name = $this->option('name') ?: $this->askValid('What should we name the client?', 'name', 'required|string|strip_tags');
        $userId = $this->option('user_id') ?: $this->askValid('Which user ID should the client be assigned to?', 'user_id', 'required|integer');
        $redirect = $this->option('redirect_uri') ?: $this->askValid('Where should we redirect the request after authorization?', 'redirect_uri', 'required|string|url|max:1500', url('/auth/callback'));

        $providers = array_keys(config('auth.providers'));
        $provider = $this->option('provider') ?: $this->choice(
            'Which user provider should this client use to retrieve users?',
            $providers,
            in_array('users', $providers) ? 'users' : null
        );

        $client = app('rinvex.oauth.client')->create([
            'user_id' => $userId,
            'name' => $name,
            'secret' => Str::random(40),
            'provider' => $provider,
            'redirect' => $redirect,
            'grant_type' => 'password',
        ]);

        $this->info('Password grant client created successfully.');

        $this->outputClientDetails($client);
    }

    /**
     * Create a client credentials grant client.
     *
     * @return void
     */
    protected function createClientCredentialsClient()
    {
        $this->alert('Create Client Credentials Client');

        $name = $this->option('name') ?: $this->askValid('What should we name the client?', 'name', 'required|string|strip_tags');
        $userId = $this->option('user_id') ?: $this->askValid('Which user ID should the client be assigned to?', 'user_id', 'required|integer');

        $providers = array_keys(config('auth.providers'));
        $provider = $this->option('provider') ?: $this->choice(
            'Which user provider should this client use to retrieve users?',
            $providers,
            in_array('users', $providers) ? 'users' : null
        );

        $client = app('rinvex.oauth.client')->create([
            'user_id' => $userId,
            'name' => $name,
            'secret' => Str::random(40),
            'provider' => $provider,
            'redirect' => null,
            'grant_type' => 'client_credentials',
        ]);

        $this->info('New client created successfully.');

        $this->outputClientDetails($client);
    }

    /**
     * Create a authorization code client.
     *
     * @return void
     */
    protected function createAuthorizationCodeClient()
    {
        $this->alert('Create Authorization Code Client');

        $name = $this->option('name') ?: $this->askValid('What should we name the client?', 'name', 'required|string|strip_tags');
        $userId = $this->option('user_id') ?: $this->askValid('Which user ID should the client be assigned to?', 'user_id', 'required|integer');
        $redirect = $this->option('redirect_uri') ?: $this->askValid('Where should we redirect the request after authorization?', 'redirect_uri', 'required|string|url|max:1500', url('/auth/callback'));

        $providers = array_keys(config('auth.providers'));
        $provider = $this->option('provider') ?: $this->choice(
            'Which user provider should this client use to retrieve users?',
            $providers,
            in_array('users', $providers) ? 'users' : null
        );

        $client = app('rinvex.oauth.client')->create([
            'user_id' => $userId,
            'name' => $name,
            'secret' => ! $this->option('public') ? Str::random(40) : null,
            'provider' => $provider,
            'redirect' => $redirect,
            'grant_type' => 'authorization_code',
        ]);

        $this->info('New client created successfully.');

        $this->outputClientDetails($client);
    }

    /**
     * Output the client's ID and secret key.
     *
     * @param \Rinvex\OAuth\Models\Client $client
     *
     * @return void
     */
    protected function outputClientDetails(Client $client)
    {
        $this->line('<comment>Here is your new client secret. This is the only time it will be shown so don\'t lose it!</comment>');
        $this->line('');

        $this->line('<comment>Client ID:</comment> '.$client->getRouteKey());
        $this->line('<comment>Client secret:</comment> '.$client->plainSecret);
    }
}
