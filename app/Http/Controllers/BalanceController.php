<?php namespace App\Http\Controllers;

use App\User;
use App\SlackEvent;
use App\SlackClient;
use App\UserBalance;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BalanceController extends Controller
{

    public function getAll (Request $request)
    {

        $event = new SlackEvent ($request);

        $balances = UserBalance::orderBy ("amount", "asc")
            ->get ();

        $responseString = "Balansen:\n";
        foreach($balances->all () as $balance)
        {

            $user = User::where ("id", "=", $balance->user_id)
                ->first ();

            $userBalance = (double) $balance->amount;

            $responseString .= "@$user->username - *€$userBalance*\n";

        }

        $bot = new SlackClient ();
        $bot->sendEphemeralMessageToChannel (env ("SLACK_BOT_CHANNEL"), $responseString, $event->userId);

        return response (null, 200);

    }

}