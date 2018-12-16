<?php
namespace Robsonala\CryptoUser\Models;

use Illuminate\Database\Eloquent\Model;

class CryptoPassphrases extends Model
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        
        $this->setTable(config('crypto-user.tables.passphrases'));
    }

    protected $fillable = [
        'user_id', 'related_user_id', 'passphrase'
    ];

    public function user()
    {
        return $this->belongsTo(
            config('crypto-user.user.model')
        );
    }

    public function relatedUser()
    {
        return $this->belongsTo(
            config('crypto-user.user.model', 'related_user_id')
        );
    }
}