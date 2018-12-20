<?php
namespace Robsonala\CryptoUser\Test\Models;

use Illuminate\Database\Eloquent\Model;
use Robsonala\CryptoUser\Traits\CryptData;

class Todo extends Model
{
    use CryptData;
    protected $crypt_attributes = ['text'];

    protected $fillable = ['title', 'text'];
    public $timestamps = false;
    protected $table = 'todo';
}