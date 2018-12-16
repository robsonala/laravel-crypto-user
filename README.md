# Example usage:

## RegisterController.php
```
...
use Robsonala\CryptoUser\Services\KeyPair;
use Robsonala\CryptoUser\Services\CryptoUser;
use Robsonala\CryptoUser\Models\CryptoKeys;
use Robsonala\CryptoUser\Models\CryptoPassphrases;
...
$keyPair = new KeyPair();
$keyPair->generate($data['password']);

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
...
```

## LoginController.php
```
...
use Robsonala\CryptoUser\Services\KeyPair;
use Robsonala\CryptoUser\Services\CryptoUser;
...
$keyPair = new KeyPair();
$keyPair->loadPublic($user->cryptoKeys->public_key);
$keyPair->loadPrivate($user->cryptoKeys->private_key, $request->password);

CryptoUser::setSessionPassphrase($keyPair->decrypt($user->cryptoPassphrase->passphrase));
...
```

## [ANY].php // Changing password
```
...
use Robsonala\CryptoUser\Services\KeyPair;
use Robsonala\CryptoUser\Models\CryptoKeys;
...
$new = $request->get('new-password');

$user = Auth::user();

$keyPair = new KeyPair();
$keyPair->loadPublic($user->cryptoKeys->public_key);
$keyPair->loadPrivate($user->cryptoKeys->private_key, $old);

$keyPair->setNewPassword($new);

CryptoKeys::where('user_id', $user->id)->delete();
CryptoKeys::create([
    'user_id' => $user->id, 
    'private_key' => $keyPair->getPrivateKey(),
    'public_key' => $keyPair->getPublicKey(),
]);
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