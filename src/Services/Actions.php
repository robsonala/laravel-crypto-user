<?php
namespace Robsonala\CryptoUser\Services;

use Robsonala\CryptoUser\Models\{CryptoKeys, CryptoPassphrases};
use Robsonala\Exceptions\CryptoUserException;
use Illuminate\Database\Eloquent\Model;

class Actions
{

    /**
     * Instance passphrase after user's login
     * 
     * @param Model $user       User's instance
     * @param string $password  Password
     */
    public static function login(Model $user, string $password): string
    {
        // TODO : Generate keypair if the user doesn't have one
        if (!isset($user->cryptoKeys)) {
            return "";
        }

        $keyPair = new KeyPair([
            'publickey' => $user->cryptoKeys->public_key,
            'privatekey' => $user->cryptoKeys->private_key,
            'password' => $password
        ]);

        CryptoUser::setSessionPassphrase($keyPair->decrypt($user->cryptoPassphrase->passphrase));

        return CryptoUser::getSessionPassphrase();
    }

    /**
     * Create keys after user's creation
     * 
     * @param Model $user       User's instance
     * @param string $password  Password
     */
    public static function register(Model $user, string $password): string
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

        return CryptoUser::getSessionPassphrase();
    }

    /**
     * Re-encrypt passphrase after update password
     * 
     * @param Model $user           User's instance
     * @param string $oldPassword   Old password
     * @param string $newPassword   New password
     */
    public static function updatePassword(Model $user, string $oldPassword, string $newPassword)
    {
        if (!isset($user->cryptoKeys)) {
            return;
        }

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

    /**
     * Share my passphrase with another user
     * 
     * @param Model $passphraseOwner    User that will share the passphrase
     * @param Model $user               User that will receive the passphrase
     * @param string $password          Passphrase
     */
    public static function sharePassphrase(Model $passphraseOwner, Model $user, string $passphrase = '')
    {
        if (!isset($user->cryptoKeys)) {
            return;
        }

        if (!$passphrase) {
            $passphrase = CryptoUser::getSessionPassphrase();
        }

        $keyPair = new KeyPair([
            'publickey' => $user->cryptoKeys->public_key
        ]);

        CryptoPassphrases::create([
            'user_id' => $user->id, 
            'related_user_id' => $passphraseOwner->id, 
            'passphrase' => $keyPair->encrypt($passphrase),
        ]);
    }

    /**
     * Recover passphrase for another user
     * 
     * @param Model $passphraseOwner    User that want to get the passphrase back
     * @param Model $user               User that will help
     * @param string $password          Passphrase
     */
    public static function recoverPassphrase(Model $passphraseOwner, Model $user, string $passphrase = '')
    {
        if (!isset($user->cryptoKeys) || !isset($passphraseOwner->cryptoKeys)) {
            return;
        }

        if (!$passphrase) {
            $passphrase = CryptoUser::getSessionPassphrase();
        }

        $keyPairOwner = new KeyPair([
            'publickey' => $passphraseOwner->cryptoKeys->public_key
        ]);
        $keyPairProvider = new KeyPair([
            'privatekey' => $user->cryptoKeys->private_key,
            'password' => $passphrase
        ]);

        $decryptedPassphrase = $keyPairProvider->decrypt($user->cryptoPassphrasesShared($passphraseOwner->id)->passphrase);

        CryptoPassphrases::where('user_id', $passphraseOwner->id)
            ->where('related_user_id', $passphraseOwner->id)
            ->delete();
        CryptoPassphrases::create([
            'user_id' => $passphraseOwner->id, 
            'related_user_id' => $passphraseOwner->id, 
            'passphrase' => $keyPairOwner->encrypt($decryptedPassphrase)
        ]);
    }

}