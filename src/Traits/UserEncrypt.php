<?php
namespace Robsonala\CryptoUser\Traits;

use Robsonala\CryptoUser\Models\CryptoKeys;
use Robsonala\CryptoUser\Models\CryptoPassphrases;

trait UserEncrypt
{
    public function cryptoKeys()
    {
        return $this->hasOne(CryptoKeys::class);
    }

    public function cryptoPassphrase()
    {
        return $this->hasOne(CryptoPassphrases::class)
            ->where('related_user_id', $this->id);
    }

    public function cryptoPassphrasesShared(int $userId = null)
    {
        if ($userId) {
            return $this->hasOne(CryptoPassphrases::class)
                ->whereIn('related_user_id', [$userId])
                ->first();
        } else {
            return $this->hasMany(CryptoPassphrases::class)
                ->whereNotIn('related_user_id', [$this->id]);
        }
    }
}