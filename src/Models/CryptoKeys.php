<?php
namespace Robsonala\CryptoUser\Models;

use Illuminate\Database\Eloquent\Model;

class CryptoKeys extends Model
{
    protected $primaryKey = 'user_id';
    public $incrementing = false;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        
        $this->setTable(config('crypto-user.tables.keys'));
    }

    protected $fillable = [
        'user_id', 'private_key', 'public_key'
    ];

    public function user()
    {
        return $this->belongsTo(
            config('crypto-user.user.model')
        );
    }
}