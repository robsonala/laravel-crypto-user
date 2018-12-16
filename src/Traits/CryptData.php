<?php
namespace Robsonala\CryptoUser\Traits;

use Robsonala\CryptoUser\Services\CryptoUser;

trait CryptData
{
    /**
     * @param $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);
        
        if (array_key_exists($key, array_flip($this->crypt_attributes))) {
            $value = CryptoUser::decryptText($value);
        }

        return $value;
    }

    /**
     * @param $key
     * @param $value
     */
    public function setAttribute($key, $value)
    {
        if (array_key_exists($key, array_flip($this->crypt_attributes))) {
            $value = CryptoUser::encryptText($value);
        }
        
        return parent::setAttribute($key, $value);
    }
}