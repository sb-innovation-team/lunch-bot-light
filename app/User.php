<?php namespace App;

use App\UserBalance;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{

    protected $fillable = 
    [
        'username', "slack_id", 'email',
    ];

    public function userBalance ()
    {

        return $this->hasOne ("App\UserBalance");

    }
    
    public function getBalance ()
    {

        return (double) $this->userBalance->amount;

    }

}
