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
        $bot = new SlackClient ();

        if (User::where ("slack_id", "=", $event->userId)->exists ())
        {

            $bot->sendEphemeralMessageToChannel (env ("SLACK_BOT_CHANNEL"), "Dit Slack Account staat al geregistreerd!", $event->userId);
            return response (["error" => "This user already exists :("], 400);

        }

        $extractedEmail = preg_match ("/\|(.*@socialbrothers.nl)/", $event->text, $matches);

        if ( ! isset ($matches[1]))
            return response (["errors" => "Je email adres moet een Social Brothers email adres zijn."], 400);

        $user = User::create 
        (
            [
                "username" => $event->username
            ,   "slack_id" => $event->userId
            ,   "email"    => $matches[1]
            ]
        );

        UserBalance::create 
        (
            [
                "user_id" => $user->id
            ,   "amount"  => 0
            ]
        );

        $bot->sendEphemeralMessageToChannel (env ("SLACK_BOT_CHANNEL"), "Welkom, @$user->username ($user->email)!", $user->slack_id);
        $bot->sendMessageToChannel (env ("SLACK_BOT_SURVEILLANCE_CHANNEL"), "@$user->username ($user->email) heeft zich registreert!", $user->slack_id);
        
        return response (null, 200);

    }

    
}
