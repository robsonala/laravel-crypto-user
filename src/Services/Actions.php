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
        $cryptoKeys = $user->cryptoKeys()->first();

        // TODO : Generate keypair if the user doesn't have one
        if (!isset($cryptoKeys)) {
            return "";
        }

        $keyPair = new KeyPair([
            'publickey' => $cryptoKeys->public_key,
            'privatekey' => $cryptoKeys->private_key,
            'password' => $password
        ]);

        CryptoUser::setSessionPassphrase($keyPair->decrypt($user->cryptoPassphrase()->first()->passphrase));

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
        $cryptoKeys = $user->cryptoKeys()->first();

        if (!isset($cryptoKeys)) {
            return;
        }

        $keyPair = new KeyPair([
            'publickey' => $cryptoKeys->public_key,
            'privatekey' => $cryptoKeys->private_key,
            'password' => $oldPassword
        ]);

        $keyPair->setNewPassword($newPassword);

        CryptoKeys::where('user_id', $user->id)->delete();
        CryptoKeys::create([
            'user_id' => $user->id, 
            'private_key' => $keyPair->getPrivateKey(),
            'public_key' => $keyPair->getPublicKey(),
        ]);

        return $keyPair->decrypt($user->cryptoPassphrase()->first()->passphrase);
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
        $cryptoKeys = $user->cryptoKeys()->first();

        if (!isset($cryptoKeys)) {
            return;
        }

        if (!$passphrase) {
            $passphrase = CryptoUser::getSessionPassphrase();
        }

        $keyPair = new KeyPair([
            'publickey' => $cryptoKeys->public_key
        ]);

        $model = CryptoPassphrases::where('user_id', $user->id)
            ->where('related_user_id', $passphraseOwner->id);

        if ($model->count() > 0){
            $model->update(['passphrase' => $keyPair->encrypt($passphrase)]);
        } else {
            CryptoPassphrases::create([
                'user_id' => $user->id, 
                'related_user_id' => $passphraseOwner->id, 
                'passphrase' => $keyPair->encrypt($passphrase)
            ]);
        }

        /* TODO: check `updateOrCreate`
        CryptoPassphrases::updateOrCreate([
            'user_id' => $user->id, 
            'related_user_id' => $passphraseOwner->id
        ], [
            'passphrase' => $keyPair->encrypt($passphrase),
        ]);*/
    }

    /**
     * Recover passphrase for another user
     * 
     * @param Model $passphraseOwner    User that want to get the passphrase back
     * @param Model $user               User that will help
     * @param string $password          Password
     */
    public static function recoverPassphrase(Model $passphraseOwner, Model $user, string $password = '')
    {
        $cryptoKeys = $user->cryptoKeys()->first();
        $cryptoKeysOwner = $passphraseOwner->cryptoKeys()->first();

        if (!isset($cryptoKeys) || !isset($cryptoKeysOwner)) {
            return;
        }

        $keyPairOwner = new KeyPair([
            'publickey' => $cryptoKeysOwner->public_key
        ]);
        $keyPairProvider = new KeyPair([
            'privatekey' => $cryptoKeys->private_key,
            'password' => $password
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