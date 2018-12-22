<?php
namespace Robsonala\CryptoUser\Test;

use Robsonala\CryptoUser\Services\CryptoUser;
use Robsonala\CryptoUser\Exceptions\CryptoUserException;
use Illuminate\Contracts\Encryption\DecryptException;

class ServicesCryptoUserTest extends TestCase
{
    /** @test */
    public function i_can_create_a_passphrase()
    {
        $key = CryptoUser::setSessionPassphrase();

        $this->assertEquals(32, strlen($key));
        $this->assertRegExp('/[A-Za-z0-9]{32}/', $key);
    }

    /** @test */
    public function i_can_create_setting_a_passphrase()
    {
        $k = substr(hash('sha512',rand()),0,32);
        $key = CryptoUser::setSessionPassphrase($k);

        $this->assertEquals($k, $key);
        $this->assertEquals(32, strlen($key));
        $this->assertRegExp('/[A-Za-z0-9]{32}/', $key);
    }

    /** @test */
    public function i_can_not_set_an_invalid_passphrase()
    {
        $k = hash('sha512',rand());
        try {
            CryptoUser::setSessionPassphrase($k);

            throw new \Exception('This test should fail');
        } catch (CryptoUserException $e) {
            $this->assertTrue(true);
        }
    }

    /** @test */
    public function i_can_get_passphrase()
    {
        $key = CryptoUser::setSessionPassphrase();

        $this->assertEquals($key, CryptoUser::getSessionPassphrase());
    }

    /** @test */
    public function i_can_not_get_passphrase_without_set_it()
    {
        $this->killPassphraseSession();

        try {
            CryptoUser::getSessionPassphrase();

            throw new \Exception('This test should fail');
        } catch (CryptoUserException $e) {
            $this->assertTrue(true);
        }
    }

    /** @test */
    public function i_can_encrypt_text()
    {
        CryptoUser::setSessionPassphrase();

        $plain = 'Lorem ipsum dolor sit amet';
        $crypt = CryptoUser::encryptText($plain);

        $this->assertNotEquals($plain, $crypt);
    }

    /** @test */
    public function i_can_decrypt_text()
    {
        CryptoUser::setSessionPassphrase();

        $plain = 'Lorem ipsum dolor sit amet';
        $crypt = CryptoUser::encryptText($plain);

        $this->assertEquals($plain, CryptoUser::decryptText($crypt));
    }

    /** @test */
    public function i_can_not_encrypt_text_without_passphrase()
    {
        $this->killPassphraseSession();

        try {
            $plain = 'Lorem ipsum dolor sit amet';
            CryptoUser::encryptText($plain);

            throw new \Exception('This test should fail');
        } catch (CryptoUserException $e) {
            $this->assertTrue(true);
        }
    }

    /** @test */
    public function i_can_not_decrypt_text_without_passphrase()
    {
        CryptoUser::setSessionPassphrase();
        $plain = 'Lorem ipsum dolor sit amet';
        $crypt = CryptoUser::encryptText($plain);

        $this->killPassphraseSession();

        try {
            $plain = 'Lorem ipsum dolor sit amet';
            CryptoUser::decryptText($crypt);

            throw new \Exception('This test should fail');
        } catch (CryptoUserException $e) {
            $this->assertTrue(true);
        }
    }

    /** @test */
    public function i_can_not_decrypt_text_with_different_passphrase()
    {
        CryptoUser::setSessionPassphrase();
        $plain = 'Lorem ipsum dolor sit amet';
        $crypt = CryptoUser::encryptText($plain);

        CryptoUser::setSessionPassphrase();

        try {
            CryptoUser::decryptText($crypt);

            throw new \Exception('This test should fail');
        } catch (DecryptException $e) {
            $this->assertTrue(true);
        }
    }

    /** @test */
    public function i_can_not_decrypt_text_that_is_no_encrypted()
    {
        CryptoUser::setSessionPassphrase();

        try {
            CryptoUser::decryptText('lorem ipsum dolor sit amet');

            throw new \Exception('This test should fail');
        } catch (DecryptException $e) {
            $this->assertTrue(true);
        }
    }
}