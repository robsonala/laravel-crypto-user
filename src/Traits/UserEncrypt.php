<?php
namespace Robsonala\CryptoUser\Traits;

use Robsonala\CryptoUser\Models\UserCrypto;

trait UserEncrypt
{
    public function crypto()
    {
        return $this->hasOne(UserCrypto::class);
    }
}