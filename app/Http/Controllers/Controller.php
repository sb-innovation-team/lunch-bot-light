<?php

namespace App\Http\Controllers;

use App\SlackClient;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    //
    protected $bot;

    public function __construct ()
    {

        $this->bot = new SlackClient ();

    }

}
