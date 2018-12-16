<?php
namespace Robsonala\CryptoUser\Services;

use phpseclib\Crypt\RSA;
use Illuminate\Encryption\Encrypter;

class CryptoUser
{

    public static function generateKeyPair($password)
    {
        $rsa = new RSA();
 
        $rsa->setPassword($password);
        $rsa->setPrivateKeyFormat(env('crypto-user.rsa_private_format'));
        $rsa->setPublicKeyFormat(env('crypto-user.rsa_public_format'));
        $rsa->createKey();

        return (object)[
            'private' => $rsa->getPrivateKey(),
            'public' => $rsa->getPublicKey()
        ];
    }

    public static function updatePasswordKeyPair($privateKey, $oldPassword, $newPassword)
    {
        $rsa = new RSA();
 
        $rsa->setPassword($oldPassword);
        $rsa->loadKey($privateKey);

        $rsa->setPassword($newPassword);

        return (object)[
            'private' => $rsa->getPrivateKey(),
            'public' => $rsa->getPublicKey()
        ];
    }

    public static function encryptText($value, $passphrase = null)
    {
        if (!$passphrase) {
            $passphrase = env('crypto-user.rsa_default_key');
        }

        $crypter = new Encrypter($passphrase);

        return $crypter->encrypt($value);
    }

    public static function decryptText($value, $passphrase = null)
    {
        if (!$passphrase) {
            $passphrase = env('crypto-user.rsa_default_key');
        }

        $crypter = new Encrypter($passphrase);

        return $crypter->decrypt($value);
    }

}