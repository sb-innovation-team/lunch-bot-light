<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{

    protected $fillable = 
    [
        "user_id", "mutation_type", "amount"
    ];

    public function user ()
    {

        return $this->belongsTo ("App\User");

    }
    
}