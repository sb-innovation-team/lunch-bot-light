<?php namespace App\Http\Controllers;

use App\User;
use App\SlackEvent;
use App\SlackClient;
use App\UserBalance;
use Illuminate\Http\Request;

class UserController extends Controller
{

    public function registerSlackUser (Request $request)
    {

        $event = new SlackEvent ($request);

        if (User::where ("slack_id", "=", $event->userId)->exists ())
            return response (["error" => "This user already exists :("], 400);

        $user = User::create 
        (
            [
                "username" => $event->username
            ,   "slack_id" => $event->userId
            ,   "email"    => $event->text
            ]
        );

        UserBalance::create 
        (
            [
                "user_id" => $user->id
            ,   "amount"  => 0
            ]
        );

        $bot = new SlackClient ();
        $bot->sendMessageToChannel (env ("SLACK_BOT_CHANNEL"), "Welkom, $user->username ($user->email)!");

        return response (null, 200);

    }

    
}
