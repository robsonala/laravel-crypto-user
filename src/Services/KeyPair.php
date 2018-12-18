<?php
namespace Robsonala\CryptoUser\Services;

use phpseclib\Crypt\RSA;
use Robsonala\CryptoUser\Exceptions\CryptoUserException;

class KeyPair
{
    protected $rsaPublic;
    protected $rsaPrivate;

    public function __construct(Array $options = [])
    {
        $this->rsaPublic = new RSA();
        $this->rsaPrivate = new RSA();

        if (isset($options['publickey'])) {
            $this->loadPublic($options['publickey']);
        }
        if (isset($options['privatekey'])) {
            if (!isset($options['password'])) {
                $options['password'] = null;
            }
            
            $this->loadPrivate($options['privatekey'], $options['password']);
        }
    }

    protected function getPublic(): RSA
    {
        return $this->rsaPublic;
    }

    protected function getPrivate(): RSA
    {
        return $this->rsaPrivate;
    }

    public function generate(string $password = null): self
    {
        $rsa = new RSA();

        if ($password) {
            $rsa->setPassword($password);
        }

        $rsa->setPrivateKeyFormat(env('crypto-user.rsa_private_format'));
        $rsa->setPublicKeyFormat(env('crypto-user.rsa_public_format'));

        $data = $rsa->createKey();

        $this->loadPublic($data['publickey']);
        $this->loadPrivate($data['privatekey'], $password);

        return $this;
    }

    public function setNewPassword(string $password): self
    {
        $this->getPrivate()->setPassword($password);

        return $this;
    }

    public function loadPublic(string $key): self
    {
        if (!$this->getPublic()->loadKey($key)) {
            throw new CryptoUserException('Error to import public key!');
        }

        return $this;
    }

    public function loadPrivate(string $key, string $password = null): self
    {
        if ($password) {
            $this->getPrivate()->setPassword($password);
        }
        
        if (!$this->getPrivate()->loadKey($key)) {
            throw new CryptoUserException('Error to import private key! Verify key and password.');
        }

        return $this;
    }

    public function encrypt($value): string
    {
        if (!$this->getPublicKey()) {
            throw new CryptoUserException('Public key not found!');
        }

        return $this->getPublic()->encrypt($value);
    }

    public function decrypt($value): string
    {
        if (!$this->getPrivateKey()) {
            throw new CryptoUserException('Private key not found!');
        }
        
        try {
            return $this->getPrivate()->decrypt($value);
        } catch (\ErrorException $e) {
            throw new CryptoUserException($e->getMessage());
        }
    }

    public function getPublicKey(): string
    {
        return $this->getPublic()->getPublicKey();
    }

    public function getPrivateKey(): string
    {
        return $this->getPrivate()->getPrivateKey();
    }

}