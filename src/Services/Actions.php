<?php
namespace Robsonala\CryptoUser\Services;

use Robsonala\CryptoUser\Models\{CryptoKeys, CryptoPassphrases};

class Actions
{

    public static function login($user, $password)
    {
        // TODO : Generate keypair if the user doesn't have one

        $keyPair = new KeyPair([
            'publickey' => $user->cryptoKeys->public_key,
            'privatekey' => $user->cryptoKeys->private_key,
            'password' => $password
        ]);

        CryptoUser::setSessionPassphrase($keyPair->decrypt($user->cryptoPassphrase->passphrase));
    }

    public static function register($user, $password)
    {
        $keyPair = new KeyPair();
        $keyPair->generate($password);

        CryptoUser::setSessionPassphrase();

        CryptoKeys::create([
            'user_id' => $user->id, 
            'private_key' => $keyPair->getPrivateKey(),
            'public_key' => $keyPair->getPublicKey(),
        ]);

        CryptoPassphrases::create([
            'user_id' => $user->id, 
            'related_user_id' => $user->id, 
            'passphrase' => $keyPair->encrypt(CryptoUser::getSessionPassphrase()),
        ]);
    }

    public static function updatePassword($user, $oldPassword, $newPassword)
    {
        $keyPair = new KeyPair([
            'publickey' => $user->cryptoKeys->public_key,
            'privatekey' => $user->cryptoKeys->private_key,
            'password' => $oldPassword
        ]);

        $keyPair->setNewPassword($newPassword);

        CryptoKeys::where('user_id', $user->id)->delete();
        CryptoKeys::create([
            'user_id' => $user->id, 
            'private_key' => $keyPair->getPrivateKey(),
            'public_key' => $keyPair->getPublicKey(),
        ]);
    }

}