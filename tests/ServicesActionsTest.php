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
        $user = $this->createUser(['password' => $password]);

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

        $user = $this->createUser(['password' => $password]);

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
        $user = $this->createUser(['password' => $password]);
        $passphrase = Actions::register($user, $password);

        $newPassword = uniqid();
        $passphraseAfterUpdate = Actions::updatePassword($user, $password, $newPassword);

        $user = User::find($user->id);

        // Shall work
        new KeyPair([
            'privatekey' => $user->cryptoKeys->private_key,
            'password' => $newPassword
        ]);

        $this->assertEquals($passphrase, $passphraseAfterUpdate);

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
        $passwordOwner = uniqid();
        $userOwner = $this->createUser(['password' => $passwordOwner]);
        $passphraseOwner = Actions::register($userOwner, $passwordOwner);

        $password1 = uniqid();
        $user1 = $this->createUser(['password' => $password1]);
        $passphrase1 = Actions::register($user1, $password1);
        $password2 = uniqid();
        $user2 = $this->createUser(['password' => $password2]);
        $passphrase2 = Actions::register($user2, $password2);

        // Create
        Actions::sharePassphrase($userOwner, $user2, $passphraseOwner);

        $itemsOwner = DB::table(config('crypto-user.tables')['passphrases'])
            ->where('user_id', $userOwner->id)->get();
        $items2 = DB::table(config('crypto-user.tables')['passphrases'])
            ->where('user_id', $user2->id)->get();

        $this->assertCount(1, $itemsOwner);
        $this->assertCount(2, $items2);

        $user2 = User::find($user2->id);

        $keyPair2 = new KeyPair([
            'privatekey' => $user2->cryptoKeys->private_key,
            'password' => $password2
        ]);

        $userOwnerPassprhase = $keyPair2->decrypt($user2->cryptoPassphrasesShared[0]->passphrase);
        $this->assertEquals($passphraseOwner, $userOwnerPassprhase);

        // Update
        Actions::sharePassphrase($userOwner, $user1, $passphraseOwner);
        Actions::sharePassphrase($userOwner, $user2, $passphraseOwner);

        $items1 = DB::table(config('crypto-user.tables')['passphrases'])
            ->where('user_id', $user1->id)->get();
        $items2 = DB::table(config('crypto-user.tables')['passphrases'])
            ->where('user_id', $user2->id)->get();

        $this->assertCount(2, $items1);
        $this->assertCount(2, $items2);
    }

    /** @test */
    public function i_can_reset_another_users_passphrase()
    {
        $password1 = uniqid();
        $user1 = $this->createUser(['password' => $password1]);
        $passphrase1 = Actions::register($user1, $password1);

        $password2 = uniqid();
        $user2 = $this->createUser(['password' => $password2]);
        $passphrase2 = Actions::register($user2, $password2);

        $itemsUser1 = DB::table(config('crypto-user.tables')['passphrases'])->where('user_id', $user1->id)->get();
        $itemsUser2 = DB::table(config('crypto-user.tables')['passphrases'])->where('user_id', $user2->id)->get();

        $this->assertCount(1, $itemsUser1);
        $this->assertCount(1, $itemsUser2);

        Actions::sharePassphrase($user1, $user2, $passphrase1);

        $itemsUser1 = DB::table(config('crypto-user.tables')['passphrases'])->where('user_id', $user1->id)->get();
        $itemsUser2 = DB::table(config('crypto-user.tables')['passphrases'])->where('user_id', $user2->id)->get();

        $this->assertCount(1, $itemsUser1);
        $this->assertCount(2, $itemsUser2);

        $user1 = User::find($user1->id);
        $user2 = User::find($user2->id);

        Actions::recoverPassphrase($user1, $user2, $password2);

        $user1 = User::find($user1->id);

        $keyPair1 = new KeyPair([
            'privatekey' => $user1->cryptoKeys->private_key,
            'password' => $password1
        ]);

        $newPassphrase1 = $keyPair1->decrypt($user1->cryptoPassphrase->passphrase);

        $this->assertEquals($passphrase1, $newPassphrase1);

    }
}