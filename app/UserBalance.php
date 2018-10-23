<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class UserBalance extends Model
{

    protected $fillable = 
    [
        "user_id", "amount"
    ];

}