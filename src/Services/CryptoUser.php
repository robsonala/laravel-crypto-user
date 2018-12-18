<?php
namespace Robsonala\CryptoUser\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\Session;
use Robsonala\CryptoUser\Exceptions\CryptoUserException;

class CryptoUser
{
    const PASSPHRASE_SESSION = "ROBSONALA_CRYPTOUSER_PASSPHRASE";
    const CIPHER = "AES-256-CBC";

    public static function setSessionPassphrase($value = null)
    {
        if (!$value) {
            $value = substr(hash('sha512',rand()), 0, 32);
        }

        if (!preg_match('/^[A-Za-z0-9]{32}$/', $value)) {
            throw new CryptoUserException('The passphrase must to have 32 alphanumeric characters.');
        }

        Session::put(self::PASSPHRASE_SESSION, Crypt::encryptString($value));

        return $value;
    }

    public static function getSessionPassphrase()
    {
        if (!Session::has(self::PASSPHRASE_SESSION)) {
            throw new CryptoUserException('Passphrase not set');
        }

        return Crypt::decryptString(Session::get(self::PASSPHRASE_SESSION));
    }

    public static function encryptText($value)
    {
        return (new Encrypter(self::getSessionPassphrase(), self::CIPHER))
            ->encrypt($value);
    }

    public static function decryptText($value)
    {
        return (new Encrypter(self::getSessionPassphrase(), self::CIPHER))
            ->decrypt($value);
    }

}