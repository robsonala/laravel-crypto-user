<?php
namespace Robsonala\CryptoUser\Test;

use Robsonala\CryptoUser\Services\KeyPair;
use Robsonala\CryptoUser\Exceptions\CryptoUserException;

class ServicesKeyPairTest extends TestCase
{
    /** @test */
    public function i_can_genereate_a_keypair_with_password()
    {
        $password = uniqid();

        $kp = new KeyPair();
        $ret = $kp->generate($password);

        $this->assertInstanceOf(KeyPair::class, $ret);
        $this->assertContains("-----BEGIN PUBLIC KEY-----", $kp->getPublicKey());
        $this->assertContains("-----BEGIN RSA PRIVATE KEY-----", $kp->getPrivateKey());
    }

    /** @test */
    public function i_can_genereate_a_keypair_without_password()
    {
        $kp = new KeyPair();
        $ret = $kp->generate();

        $this->assertInstanceOf(KeyPair::class, $ret);
        $this->assertContains("-----BEGIN PUBLIC KEY-----", $kp->getPublicKey());
        $this->assertContains("-----BEGIN RSA PRIVATE KEY-----", $kp->getPrivateKey());
    }

    /** @test */
    public function i_can_load_public_key()
    {
        $kp = (new KeyPair())->generate(uniqid());

        $kpNew = new KeyPair();
        $ret = $kpNew->loadPublic($kp->getPublicKey());

        $this->assertInstanceOf(KeyPair::class, $ret);
        $this->assertEquals($kp->getPublicKey(), $kpNew->getPublicKey());
    }

    /** @test */
    public function i_can_not_load_invalid_public_key()
    {
        $kp = new KeyPair();

        try {
            $kp->loadPublic(uniqid());

            throw new \Exception('This test should fail');
        } catch (CryptoUserException $e) {
            $this->assertTrue(true);
        }

        try {
            $kp->loadPublic('-----BEGIN PUBLIC KEY-----
            LOREMIPSUMDOLORSITAMET
            -----END PUBLIC KEY-----');

            throw new \Exception('This test should fail');
        } catch (CryptoUserException $e) {
            $this->assertTrue(true);
        }
    }

    /** @test */
    public function i_can_load_private_using_password()
    {
        $pw = uniqid();
        $kp = (new KeyPair())->generate($pw);

        $kpNew = new KeyPair();
        $ret = $kpNew->loadPrivate($kp->getPrivateKey(), $pw);

        $this->assertInstanceOf(KeyPair::class, $ret);
        $this->assertContains("-----BEGIN RSA PRIVATE KEY-----", $kpNew->getPrivateKey());
    }

    /** @test */
    public function i_can_load_private_key_without_password_when_it_does_not_exists()
    {
        $kp = (new KeyPair())->generate();

        $kpNew = new KeyPair();
        $ret = $kpNew->loadPrivate($kp->getPrivateKey());

        $this->assertInstanceOf(KeyPair::class, $ret);
        $this->assertContains("-----BEGIN RSA PRIVATE KEY-----", $kpNew->getPrivateKey());
    }

    /** @test */
    public function i_can_not_load_private_key_with_invalid_password()
    {
        $kp = (new KeyPair())->generate(uniqid());

        $kpNew = new KeyPair();

        try {
            $kpNew->loadPrivate($kp->getPrivateKey(), '');

            throw new \Exception('This test should fail');
        } catch (CryptoUserException $e) {
            $this->assertTrue(true);
        }

        try {
            $kpNew->loadPrivate($kp->getPrivateKey(), uniqid());

            throw new \Exception('This test should fail');
        } catch (CryptoUserException $e) {
            $this->assertTrue(true);
        }
    }

    /** @test */
    public function i_can_load_public_and_private_key_via_contructor()
    {
        $pw = uniqid();
        $kp = (new KeyPair())->generate($pw);

        $kpNew = new KeyPair([
            'publickey' => $kp->getPublicKey(),
            'privatekey' => $kp->getPrivateKey(),
            'password' => $pw
        ]);
        
        $a1 = substr($kp->getPrivateKey(),0,20);
        $a2 = substr($kpNew->getPrivateKey(),0,20);

        $this->assertEquals($kp->getPublicKey(), $kpNew->getPublicKey());
        $this->assertEquals($a1, $a2);
    }

    /** @test */
    public function i_can_update_password()
    {
        $pw = uniqid();
        $newPw = uniqid();

        $kp = (new KeyPair())->generate($pw);
        $kp->setNewPassword($newPw);

        (new KeyPair())->loadPrivate($kp->getPrivateKey(), $newPw);

        try {
            (new KeyPair())->loadPrivate($kp->getPrivateKey(), $pw);

            throw new \Exception('This test should fail');
        } catch (CryptoUserException $e) {
            $this->assertTrue(true);
        }
    }

    /** @test */
    public function i_can_encrypt_data()
    {
        $pw = uniqid();
        $kp = (new KeyPair())->generate($pw);

        $plain = 'Lorem ipsum dolor sit amet'; 

        $this->assertNotEquals($plain, $kp->encrypt($plain));
    }

    /** @test */
    public function i_can_decrypt_data()
    {
        $pw = uniqid();
        $kp = (new KeyPair())->generate($pw);

        $plain = 'Lorem ipsum dolor sit amet'; 
        $encrypted = $kp->encrypt($plain);

        $this->assertEquals($plain, $kp->decrypt($encrypted));
    }

    /** @test */
    public function i_can_not_encrypt_data_without_public_key()
    {
        $pw = uniqid();
        $kp = (new KeyPair())->generate($pw);

        $kpNew = new KeyPair([
            'privatekey' => $kp->getPrivateKey(),
            'password' => $pw
        ]);

        try {
            $plain = 'Lorem ipsum dolor sit amet'; 
            $this->assertEquals($plain, $kpNew->encrypt($plain));

            throw new \Exception('This test should fail');
        } catch (CryptoUserException $e) {
            $this->assertTrue(true);
        }
    }

    /** @test */
    public function i_can_not_decrypt_data_without_private_key()
    {
        $pw = uniqid();
        $kp = (new KeyPair())->generate($pw);
        $kp2 = new KeyPair([
            'publickey' => $kp->getPublicKey(),
        ]);

        $plain = 'Lorem ipsum dolor sit amet'; 
        $crypted = $kp->encrypt($plain);

        try {
            $kp2->decrypt($crypted);

            throw new \Exception('This test should fail');
        } catch (CryptoUserException $e) {
            $this->assertTrue(true);
        }
        
        $this->assertEquals($plain, $kp->decrypt($crypted));
    }

    /** @test */
    public function i_can_not_decrypt_data_using_different_private_key()
    {
        $pw = uniqid();
        $kp = (new KeyPair())->generate($pw);
        $kp2 = (new KeyPair())->generate($pw);

        $plain = 'Lorem ipsum dolor sit amet'; 
        $crypted = $kp->encrypt($plain);

        $this->assertEquals($plain, $kp->decrypt($crypted));
        
        try {
            $kp2->decrypt($crypted);

            throw new \Exception('This test should fail');
        } catch (CryptoUserException $e) {
            $this->assertTrue(true);
        }
    }
}