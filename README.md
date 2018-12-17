# Laravel Crypto User
Cryptography tools for Laravel linked with User's table including Eloquent support

***

## Usage:

### STEP 1 - Set 'UserEncrypt Trait' on User's model
```
...
use Robsonala\CryptoUser\Traits\UserEncrypt;
...
use Notifiable, UserEncrypt;
...
```

### STEP 2 - Create register key for users (RegisterController.php)
```
...
use Robsonala\CryptoUser\Services\Actions;
...
protected function create(array $data)
{
    $user = User::create([
        'name' => $data['name'],
        'email' => $data['email'],
        'password' => bcrypt($data['password']),
    ]);

    Actions::register($user, $data['password']);

    return $user;
}
...
```

### STEP 3 - Set key on memory when user login(LoginController.php)
```
...
use Robsonala\CryptoUser\Services\Actions;
...
protected function authenticated($request, $user)
{
    Actions::login($user, $request->password);
}
...
```

## STEP 4 - Set the 'CryptData Trait' on every desired model
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

## STEP 5 - You must the key always you update the user's password
```
...
use Robsonala\CryptoUser\Services\Actions;
...
$old = $request->get('current-password');
$new = $request->get('new-password');

$user = Auth::user();

Actions::updatePassword($user, $old, $new);
...
```

License
----

MIT