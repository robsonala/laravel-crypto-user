<?php
namespace Robsonala\CryptoUser;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Collection;
use Illuminate\Filesystem\Filesystem;
use Robsonala\CryptoUser\Services\CryptoUser;

class CryptoUserProvider extends ServiceProvider
{
    /**
     * Boot method.
     *
     * @return void
     */
    public function boot(Filesystem $filesystem)
    {
        $this->publishes([
            __DIR__.'/../config/crypto-user.php' => config_path('crypto-user.php'),
        ], 'config');

        $this->publishes([
            __DIR__.'/../database/migrations/create_crypto_user_tables.php.stub' => database_path(sprintf('migrations/%s_%s_%s_000000_create_crypto_user_tables.php', date('Y'), date('m'), date('d')))
        ], 'migrations');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/crypto-user.php',
            'crypto-user'
        );

    }
}