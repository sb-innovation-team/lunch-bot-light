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

        $balances = UserBalance::orderBy ("amount", "desc")
            ->get ();

        $responseString = "Balansen:\n";

        foreach ($balances as $balance)
        {

            $user = User::find ($balance->user_id);
            $responseString .= "@$user->username | *€" . $balance->getAmount () . "*\n";

        }

        $this->bot->sendEphemeralMessageToChannel (env ("SLACK_BOT_CHANNEL"), $responseString, $event->userId);

        return response (null, 200);

    }

}