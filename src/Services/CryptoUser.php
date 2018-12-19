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

    public static function setSessionPassphrase(string $value = null): string
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

    public static function getSessionPassphrase(): string
    {
        if (!Session::has(self::PASSPHRASE_SESSION)) {
            throw new CryptoUserException('Passphrase not set');
        }

        return Crypt::decryptString(Session::get(self::PASSPHRASE_SESSION));
    }

    public static function encryptText(string $value): string
    {
        return (new Encrypter(self::getSessionPassphrase(), self::CIPHER))
            ->encrypt($value);
    }

    public static function decryptText(string $value): string
    {
        return (new Encrypter(self::getSessionPassphrase(), self::CIPHER))
            ->decrypt($value);
    }

}