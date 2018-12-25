<?php
namespace Robsonala\CryptoUser\Test\Helpers;

use Illuminate\Support\Facades\Session;
use Robsonala\CryptoUser\Test\Models\User;

trait Unit
{

    protected function killPassphraseSession()
    {
        Session::forget('ROBSONALA_CRYPTOUSER_PASSPHRASE');
    }

    protected function createUser(array $data = []): User
    {
        $data = $data + [
            'email' => uniqid() . '@user.com',
            'password' => bcrypt(uniqid())
        ];

        return User::create($data);
    }
}