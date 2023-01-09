<?php

declare(strict_types=1);

namespace Rinvex\Oauth\Console\Commands;

use phpseclib3\Crypt\RSA;
use Illuminate\Support\Arr;
use Illuminate\Console\Command;
use phpseclib\Crypt\RSA as LegacyRSA;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'rinvex:oauth:keys')]
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
     * @return int
     */
    public function handle()
    {
        $this->alert($this->description);

        [$publicKey, $privateKey] = [
            self::keyPath('oauth-public.key'),
            self::keyPath('oauth-private.key'),
        ];

        if ((file_exists($publicKey) || file_exists($privateKey)) && ! $this->option('force')) {
            $this->error('Encryption keys already exist. Use the --force option to overwrite them.');

            return 1;
        }

        if (class_exists(LegacyRSA::class)) {
            $keys = (new LegacyRSA())->createKey($this->input ? (int) $this->option('length') : 4096);

            file_put_contents($publicKey, Arr::get($keys, 'publickey'));
            file_put_contents($privateKey, Arr::get($keys, 'privatekey'));
        } else {
            $key = RSA::createKey($this->input ? (int) $this->option('length') : 4096);

            file_put_contents($publicKey, (string) $key->getPublicKey());
            file_put_contents($privateKey, (string) $key);
        }

        $this->info('Encryption keys generated successfully.');

        return 0;
    }

    /**
     * The location of the encryption keys.
     *
     * @param string $file
     *
     * @return string
     */
    public static function keyPath($file)
    {
        return storage_path($file);
    }
}
