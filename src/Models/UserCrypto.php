<?php
namespace Robsonala\CryptoUser\Models;

use Illuminate\Database\Eloquent\Model;
use Robsonala\CryptoUser\Services\CryptoUser as CryptoService;

class UserCrypto extends Model
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(config('crypto-user.table_name'));
    }

    protected $fillable = [
        'user_id', 'passphrase', 'private_key', 'public_key'
    ];

    public function user()
    {
        return $this->belongsTo(
            config('crypto-user.user.model')
        );
    }
}