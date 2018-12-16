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

    protected function getPublic()
    {
        return $this->rsaPublic;
    }

    protected function getPrivate()
    {
        return $this->rsaPrivate;
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

    public function setNewPassword($password)
    {
        $this->getPrivate()->setPassword($password);

        return $this;
    }

    public function loadPublic($key)
    {
        $this->getPublic()->loadKey($key);

        return $this;
    }

    public function loadPrivate($key, $password)
    {
        $this->getPrivate()->setPassword($password);
        $this->getPrivate()->loadKey($key);

        return $this;
    }

    public function encrypt($value)
    {
        return $this->getPublic()->encrypt($value);
    }

    public function decrypt($value)
    {
        return $this->getPrivate()->decrypt($value);
    }

    public function getPublicKey()
    {
        return $this->getPublic()->getPublicKey();
    }

    public function getPrivateKey()
    {
        return $this->getPrivate()->getPrivateKey();
    }

}