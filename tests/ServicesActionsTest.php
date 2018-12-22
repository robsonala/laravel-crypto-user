<?php
namespace Robsonala\CryptoUser\Test;

use Robsonala\CryptoUser\Test\Models\User;
use Robsonala\CryptoUser\Services\{Actions, CryptoUser, KeyPair};
use Robsonala\CryptoUser\Exceptions\CryptoUserException;
use Robsonala\CryptoUser\Models\{CryptoKeys, CryptoPassphrases};
use Illuminate\Support\Facades\DB;

class ServicesActionsTest extends TestCase
{

    /** @test */
    public function i_can_register()
    {
        $password = uniqid();
        $user = User::create(['email' => uniqid() . '@user.com', 'password' => bcrypt($password)]);

        Actions::register($user, $password);

        $this->assertNotEmpty(CryptoUser::getSessionPassphrase());
        $this->assertInstanceOf(CryptoKeys::class, $user->cryptoKeys);
        $this->assertInstanceOf(CryptoPassphrases::class, $user->cryptoPassphrase);

        $this->assertContains('PRIVATE KEY', $user->cryptoKeys->private_key);
        $this->assertNotEmpty($user->cryptoPassphrase->passphrase);

    }

    /** @test */
    public function i_can_login()
    {
        $password = uniqid();
        $passphrase = substr(hash('sha512',rand()), 0, 32);

        $user = User::create(['email' => uniqid() . '@user.com', 'password' => bcrypt($password)]);

        $keyPair = new KeyPair();
        $keyPair->generate($password);

        $userCryptoKey = CryptoKeys::create([
            'user_id' => $user->id, 
            'private_key' => $keyPair->getPrivateKey(),
            'public_key' => $keyPair->getPublicKey(),
        ]);

        $userCryptoPassphrase = CryptoPassphrases::create([
            'user_id' => $user->id, 
            'related_user_id' => $user->id, 
            'passphrase' => $keyPair->encrypt($passphrase),
        ]);

        Actions::login($user, $password);

        $this->assertEquals(CryptoUser::getSessionPassphrase(), $passphrase);
    }

    /** @test */
    public function i_can_update_password()
    {
        $password = uniqid();
        $user = User::create(['email' => uniqid() . '@user.com', 'password' => bcrypt($password)]);
        Actions::register($user, $password);

        $newPassword = uniqid();
        Actions::updatePassword($user, $password, $newPassword);

        $user = User::find($user->id);

        // Shall work
        new KeyPair([
            'privatekey' => $user->cryptoKeys->private_key,
            'password' => $newPassword
        ]);

        // Shall fail
        try {
            new KeyPair([
                'privatekey' => $user->cryptoKeys->private_key,
                'password' => $password
            ]);

            throw new \Exception('This test should fail');
        } catch (CryptoUserException $e) {
            $this->assertTrue(true);
        }
    }

    /** @test */
    public function i_can_share_my_passphrase_with_another_user()
    {
        $password1 = uniqid();
        $user1 = User::create(['email' => uniqid() . '@user.com', 'password' => bcrypt($password1)]);
        $passphrase1 = Actions::register($user1, $password1);

        $password2 = uniqid();
        $user2 = User::create(['email' => uniqid() . '@user.com', 'password' => bcrypt($password2)]);
        $passphrase2 = Actions::register($user2, $password2);

        Actions::sharePassphrase($user1, $user2, $passphrase1);

        $items1 = DB::table(config('crypto-user.tables')['passphrases'])
            ->where('user_id', $user1->id)->get();
        $items2 = DB::table(config('crypto-user.tables')['passphrases'])
            ->where('user_id', $user2->id)->get();

        $this->assertCount(1, $items1);
        $this->assertCount(2, $items2);

        $user2 = User::find($user2->id);

        $keyPair1 = new KeyPair([
            'privatekey' => $user1->cryptoKeys->private_key,
            'password' => $password1
        ]);
        $keyPair2 = new KeyPair([
            'privatekey' => $user2->cryptoKeys->private_key,
            'password' => $password2
        ]);

        $user1Passprhase = $keyPair2->decrypt($user2->cryptoPassphrasesShared[0]->passphrase);

        $this->assertEquals($passphrase1, $user1Passprhase);
    }
}