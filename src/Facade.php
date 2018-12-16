<?php
namespace Robsonala\CryptoUser;

use Robsonala\CryptoUser\Services\CryptoUser;

class Facade extends \Illuminate\Support\Facades\Facade
{
    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor()
    {
        return CryptoUser::class;
    }
}