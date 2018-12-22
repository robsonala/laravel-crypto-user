<?php
namespace Robsonala\CryptoUser\Test;

use Robsonala\CryptoUser\Exceptions\CryptoUserException;
use Robsonala\CryptoUser\Models\CryptoKeys;
use Robsonala\CryptoUser\Models\CryptoPassphrases;

use Robsonala\CryptoUser\Test\Models\User;

class TraitsUserEncryptTest extends TestCase
{
    /** @test */
    public function i_can_see_my_keys()
    {
        $user = User::create(['email' => uniqid() . '@user.com', 'password' => bcrypt(uniqid())]);

        $private = uniqid();
        $public = uniqid();
        CryptoKeys::create([
            'user_id' => $user->id, 
            'private_key' => $private,
            'public_key' => $public
        ]);

        $user = User::find($user->id);

        $this->assertEquals($private, $user->cryptoKeys->private_key);
        $this->assertEquals($public, $user->cryptoKeys->public_key);
    }

    /** @test */
    public function i_can_see_my_passphrase()
    {
        $user = User::create(['email' => uniqid() . '@user.com', 'password' => bcrypt(uniqid())]);

        $passphrase = uniqid();
        CryptoPassphrases::create([
            'user_id' => $user->id,
            'related_user_id' => $user->id,
            'passphrase' => $passphrase
        ]);

        $user = User::find($user->id);

        $this->assertEquals($passphrase, $user->cryptoPassphrase->passphrase);
    }

    /** @test */
    public function i_can_see_passphrase_that_have_been_shared_with_me()
    {
        $user1 = User::create(['email' => uniqid() . '@user.com', 'password' => bcrypt(uniqid())]);
        $user2 = User::create(['email' => uniqid() . '@user.com', 'password' => bcrypt(uniqid())]);
        $user3 = User::create(['email' => uniqid() . '@user.com', 'password' => bcrypt(uniqid())]);
        
        CryptoPassphrases::create([
            'user_id' => $user1->id,
            'related_user_id' => $user1->id,
            'passphrase' => uniqid()
        ]);
        CryptoPassphrases::create([
            'user_id' => $user2->id,
            'related_user_id' => $user2->id,
            'passphrase' => uniqid()
        ]);
        CryptoPassphrases::create([
            'user_id' => $user3->id,
            'related_user_id' => $user3->id,
            'passphrase' => uniqid()
        ]);

        CryptoPassphrases::create([
            'user_id' => $user1->id,
            'related_user_id' => $user2->id,
            'passphrase' => uniqid()
        ]);
        CryptoPassphrases::create([
            'user_id' => $user1->id,
            'related_user_id' => $user3->id,
            'passphrase' => uniqid()
        ]);

        CryptoPassphrases::create([
            'user_id' => $user3->id,
            'related_user_id' => $user2->id,
            'passphrase' => uniqid()
        ]);

        $user1 = User::find($user1->id);
        $user2 = User::find($user2->id);
        $user3 = User::find($user3->id);

        $this->assertCount(2, $user1->cryptoPassphrasesShared);
        $this->assertCount(0, $user2->cryptoPassphrasesShared);
        $this->assertCount(1, $user3->cryptoPassphrasesShared);
    }
}