#Example usage:

## RegisterController.php
```
...
use Robsonala\CryptoUser\Services\KeyPair;
use Robsonala\CryptoUser\Services\CryptoUser;
use Robsonala\CryptoUser\Models\UserCrypto;
...
$keyPair = new KeyPair();
$keyPair->generate($data['password']);

CryptoUser::setSessionPassphrase();

UserCrypto::create([
    'user_id' => $user->id, 
    'passphrase' => $keyPair->encrypt(CryptoUser::getSessionPassphrase()),
    'private_key' => $keyPair->getPrivateKey(),
    'public_key' => $keyPair->getPublicKey(),
]);
...
```

## LoginController.php
```
...
use Robsonala\CryptoUser\Services\KeyPair;
use Robsonala\CryptoUser\Services\CryptoUser;
...
$keyPair = new KeyPair();
$keyPair->loadPublic($user->crypto->public_key);
$keyPair->loadPrivate($user->crypto->private_key, $request->password);

CryptoUser::setSessionPassphrase($keyPair->decrypt($user->crypto->passphrase));
...
```

## User.php (Model)
```
...
use Robsonala\CryptoUser\Traits\UserEncrypt;
...
use Notifiable, UserEncrypt;
...
```

## Any Model
```
...
use Robsonala\CryptoUser\Traits\CryptData;
...
use CryptData;

protected $crypt_attributes = [
    'name'
];
...
```