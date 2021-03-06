<?php
namespace Robsonala\CryptoUser\Test;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Orchestra\Testbench\TestCase as Orchestra;
use Robsonala\CryptoUser\CryptoUserProvider;
use Robsonala\CryptoUser\Test\Models\User;
use Robsonala\CryptoUser\Test\Helpers\Unit;

abstract class TestCase extends Orchestra
{
    use Unit;

    public function setUp()
    {
        parent::setUp();

        $this->setUpDatabase($this->app);
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            CryptoUserProvider::class,
        ];
    }

    /**
     * Set up the environment.
     *
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
        // Use test User model for users provider
        $app['config']->set('auth.providers.users.model', User::class);
    }

    /**
     * Set up the database.
     *
     * @param \Illuminate\Foundation\Application $app
     */
    protected function setUpDatabase($app)
    {
        $app['db']
            ->connection()
            ->getSchemaBuilder()
            ->create('users', function (Blueprint $table) {
                $table->increments('id');
                $table->string('email')->unique();
                $table->string('password');
            });

        $app['db']
            ->connection()
            ->getSchemaBuilder()
            ->create('todo', function (Blueprint $table) {
                $table->increments('id');
                $table->string('title');
                $table->string('text');
            });

        include_once __DIR__.'/../database/migrations/create_crypto_user_tables.php.stub';
        
        (new \CreateCryptoUserTables())->up();
    }
}