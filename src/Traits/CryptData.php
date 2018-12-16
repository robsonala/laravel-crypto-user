<?php
namespace Robsonala\CryptoUser\Traits;

use Illuminate\Contracts\Encryption\DecryptException;
use Robsonala\CryptoUser\Services\CryptoUser;

trait CryptData
{
    protected $crypt_attributes = [];
    protected $crypt_passphrase = null;

    /**
     * @param $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);
        
        if (env('crypto-user.enabled')) {
            try {
                if (array_key_exists($key, array_flip($this->crypt_attributes))) {
                    $value = CryptoUser::decryptText($value, $this->crypt_passphrase);
                }
            } catch (DecryptException $e) {
            }
        }

        return $value;
    }

    /**
     * @param $key
     * @param $value
     */
    public function setAttribute($key, $value)
    {
        if (env('crypto-user.enabled')) {
            if (array_key_exists($key, array_flip($this->crypt_attributes))) {
                $value = CryptoUser::encryptText($value, $this->crypt_passphrase);
            }
        }
        
        return parent::setAttribute($key, $value);
    }
}