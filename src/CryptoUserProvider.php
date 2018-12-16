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
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/crypto-user.php' => config_path('crypto-user.php'),
        ], 'config');

        $this->publishes([
            __DIR__.'/../database/migrations/create_crypto_user_tables.php.stub' => $this->getMigrationFileName($filesystem),
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

        /*
        |--------------------------------------------------------------------------
        | Register the Services
        |--------------------------------------------------------------------------
        */

        $this->app->singleton('CryptoUser', function () {
            return new CryptoUser();
        });

        $this->app->alias(CryptoUser::class, 'CryptoUser');

    } 

    /**
     * Returns existing migration file if found, else uses the current timestamp.
     *
     * @param Filesystem $filesystem
     * @return string
     */
    protected function getMigrationFileName(Filesystem $filesystem): string
    {
        $timestamp = date('Y_m_d_His');
        return Collection::make($this->app->databasePath().DIRECTORY_SEPARATOR.'migrations'.DIRECTORY_SEPARATOR)
            ->flatMap(function ($path) use ($filesystem) {
                return $filesystem->glob($path.'*_create_crypto_user_tables.php');
            })->push($this->app->databasePath()."/migrations/{$timestamp}_create_crypto_user_tables.php")
            ->first();
    }
}