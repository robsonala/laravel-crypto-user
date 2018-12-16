<?php
namespace Robsonala\CryptoUser\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\Session;

class CryptoUser
{
    const PASSPHRASE_SESSION = "ROBSONALA_CRYPTOUSER_PASSPHRASE";
    const CIPHER = "AES-256-CBC";

    public static function setSessionPassphrase($value = null)
    {
        if (!$value) {
            $value = substr(hash('sha512',rand()),0,32);
        }

        Session::put(self::PASSPHRASE_SESSION, Crypt::encryptString($value));
    }

    public static function getSessionPassphrase()
    {
        return Crypt::decryptString(Session::get(self::PASSPHRASE_SESSION));
    }

    public static function encryptText($value)
    {
        $crypter = new Encrypter(self::getSessionPassphrase(), self::CIPHER);

        return $crypter->encrypt($value);
    }

    public static function decryptText($value)
    {
        $crypter = new Encrypter(self::getSessionPassphrase(), self::CIPHER);

        return $crypter->decrypt($value);
    }

}