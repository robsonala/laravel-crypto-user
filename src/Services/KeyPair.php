<?php
namespace Robsonala\CryptoUser\Services;

use phpseclib\Crypt\RSA;

class KeyPair
{
    protected $rsaPublic;
    protected $rsaPrivate;

    public function __construct()
    {
        $this->rsaPublic = new RSA();
        $this->rsaPrivate = new RSA();
    }

    public function generate($password)
    {
        $rsa = new RSA();
        $rsa->setPassword($password);
        $rsa->setPrivateKeyFormat(env('crypto-user.rsa_private_format'));
        $rsa->setPublicKeyFormat(env('crypto-user.rsa_public_format'));

        $data = $rsa->createKey();

        $this->loadPublic($data['publickey']);
        $this->loadPrivate($data['privatekey'], $password);

        return $this;
    }

    public function loadPublic($key)
    {
        $this->rsaPublic->loadKey($key);

        return $this;
    }

    public function loadPrivate($key, $password)
    {
        $this->rsaPrivate->setPassword($password);
        $this->rsaPrivate->loadKey($key);

        return $this;
    }

    public function encrypt($value)
    {
        return $this->rsaPublic->encrypt($value);
    }

    public function decrypt($value)
    {
        return $this->rsaPrivate->decrypt($value);
    }

    public function getPublicKey()
    {
        return $this->rsaPublic->getPublicKey();
    }

    public function getPrivateKey()
    {
        return $this->rsaPrivate->getPrivateKey();
    }

}