<?php
namespace Robsonala\CryptoUser\Models;

use Illuminate\Database\Eloquent\Model;
use Robsonala\CryptoUser\Services\CryptoUser as CryptoService;

class CryptoUser extends Model
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(config('crypto-user.table_name'));
    }

    public function user()
    {
        return $this->hasOne(
            config('crypto-user.user.model')
        );
    }
    
    /**
     * @param  string  $value
     * @return void
     */  
    public function setPassphraseAttribute($value)
    {
        $this->attributes['passphrase'] = CryptoService::encryptText($value, isset($this->user->crypt_passphrase) ? $this->user->crypt_passphrase : null);
    }

    /**
     * @param  string  $value
     * @return string
     */
    public function getPassphraseAttribute()
    {
        return CryptoService::decryptText($this->passphrase, isset($this->user->crypt_passphrase) ? $this->user->crypt_passphrase : null);
    }
}