<?php

declare(strict_types=1);

namespace Rinvex\OAuth\Console\Commands;

use Rinvex\OAuth\OAuth;
use phpseclib\Crypt\RSA;
use Illuminate\Support\Arr;
use Illuminate\Console\Command;

class KeysCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rinvex:oauth:keys
                                      {--force : Overwrite keys they already exist}
                                      {--length=4096 : The length of the private key}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create the encryption keys for API authentication';

    /**
     * Execute the console command.
     *
     * @param \phpseclib\Crypt\RSA $rsa
     *
     * @return void
     */
    public function handle(RSA $rsa)
    {
        [$publicKey, $privateKey] = [
            OAuth::keyPath('oauth-public.key'),
            OAuth::keyPath('oauth-private.key'),
        ];

        if ((file_exists($publicKey) || file_exists($privateKey)) && ! $this->option('force')) {
            $this->error('Encryption keys already exist. Use the --force option to overwrite them.');
        } else {
            $keys = $rsa->createKey($this->input ? (int) $this->option('length') : 4096);

            file_put_contents($publicKey, Arr::get($keys, 'publickey'));
            file_put_contents($privateKey, Arr::get($keys, 'privatekey'));

            $this->info('Encryption keys generated successfully.');
        }
    }
}
