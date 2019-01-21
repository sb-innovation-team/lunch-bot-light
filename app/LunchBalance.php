<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class LunchBalance extends Model
{

    protected $fillable = 
    [
        "amount"
    ];

    public function getBalance ()
    {

        return (double) $this->amount;

    }
    
}