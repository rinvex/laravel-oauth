<?php

declare(strict_types=1);

namespace Rinvex\Oauth\Console\Commands;

use Illuminate\Support\Carbon;
use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'rinvex:oauth:purge')]
class PurgeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rinvex:oauth:purge
                            {--revoked : Only purge revoked tokens and authentication codes}
                            {--expired : Only purge expired tokens and authentication codes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Purge revoked and / or expired tokens and authentication codes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expired = Carbon::now()->subDays(7);

        if (($this->option('revoked') && $this->option('expired')) ||
            (! $this->option('revoked') && ! $this->option('expired'))) {
            app('rinvex.oauth.access_token')->where('is_revoked', true)->orWhereDate('expires_at', '<', $expired)->delete();
            app('rinvex.oauth.auth_code')->where('is_revoked', true)->orWhereDate('expires_at', '<', $expired)->delete();
            app('rinvex.oauth.refresh_token')->where('is_revoked', true)->orWhereDate('expires_at', '<', $expired)->delete();

            $this->info('Purged revoked items and items expired for more than seven days.');
        } elseif ($this->option('revoked')) {
            app('rinvex.oauth.access_token')->where('is_revoked', true)->delete();
            app('rinvex.oauth.auth_code')->where('is_revoked', true)->delete();
            app('rinvex.oauth.refresh_token')->where('is_revoked', true)->delete();

            $this->info('Purged revoked items.');
        } elseif ($this->option('expired')) {
            app('rinvex.oauth.access_token')->whereDate('expires_at', '<', $expired)->delete();
            app('rinvex.oauth.auth_code')->whereDate('expires_at', '<', $expired)->delete();
            app('rinvex.oauth.refresh_token')->whereDate('expires_at', '<', $expired)->delete();

            $this->info('Purged items expired for more than seven days.');
        }
    }
}
