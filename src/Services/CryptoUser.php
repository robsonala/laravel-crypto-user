<?php
namespace Robsonala\CryptoUser\Services;

use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\Session;

class CryptoUser
{
    const PASSPHRASE_SESSION = "ROBSONALA_CRYPTOUSER_PASSPHRASE";

    public static function setSessionPassphrase($value = null)
    {
        if (!$value) {
            $value = substr(hash('sha512',rand()),0,32);
        }

        Session::put(self::PASSPHRASE_SESSION, $value);
    }

    public static function getSessionPassphrase()
    {
        return Session::get(self::PASSPHRASE_SESSION);
    }

    public static function encryptText($value)
    {
        $crypter = new Encrypter(self::getSessionPassphrase(), 'AES-256-CBC');

        return $crypter->encrypt($value);
    }

    public static function decryptText($value)
    {
        $crypter = new Encrypter(self::getSessionPassphrase(), 'AES-256-CBC');

        return $crypter->decrypt($value);
    }

}