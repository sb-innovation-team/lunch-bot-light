<?php namespace App\Http\Controllers;

use App\User;
use App\Eater;
use Carbon\Carbon;
use App\SlackEvent;
use App\SlackClient;
use App\Transaction;
use App\UserBalance;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class LunchController extends Controller
{

    public function signUp (Request $request)
    {

        $event = new SlackEvent ($request);
        $bot   = new SlackClient ();

        $user = User::where ("slack_id", "=", $event->userId)
            ->first ();
        
        $balance = UserBalance::where ("user_id", "=", $user->id)
            ->orderBy ("created_at", "desc")
            ->first ();

        $lastEater = Eater::where ("user_id", "=", $user->id)
            ->orderBy ("created_at", "desc")
            ->first ();

        if ($lastEater)
        {

            if ($lastEater->created_at->isToday ())
            {

                $bot->sendEphemeralMessageToChannel (env ("SLACK_BOT_CHANNEL"), "Je staat al ingeschreven voor deze lunch, @$user->username!", $user->slack_id);
                return response (null, 200);

            }

        }

        $balance->amount -= (double) env ("LUNCH_COST_IN_EUROS");
        $balance->save ();

        Eater::create (["user_id" => $user->id]);        

        Transaction::create 
        (
            [
                "user_id"       => $user->id
            ,   "mutation_type" => "withdrawl"
            ,   "amount"        => (double) env ("LUNCH_COST_IN_EUROS")
            ]
        );

        $bot->sendEphemeralMessageToChannel (env ("SLACK_BOT_CHANNEL"), "Je staat ingeschreven voor de lunch, @$user->username, je saldo staat nu op *€$balance->amount*.", $user->slack_id);

    }

    public function overview (Request $request)
    {

        $event = new SlackEvent ($request);
        $bot   = new SlackClient ();

        $eaters = Eater::whereDate ("created_at", Carbon::today ())
            ->get ();

        $responseString = "Vandaag eet mee:";

        foreach ($eaters as $eater)
        {

            $user = User::find ($eater->user_id);
            $balance = (double) UserBalance::where ("user_id", "=", $user->id)
                ->orderBy ("created_at", "desc")
                ->first ()
                ->amount;

            $responseString .= "\n@$user->username | Saldo: *€$balance*";

        }

        $budget = (double) 3 * $eaters->count ();
        $responseString .= "\n\nHet budget voor de lunch is: *€$budget*";

        $bot->sendEphemeralMessageToChannel (env ("SLACK_BOT_CHANNEL"), $responseString, $event->userId);

    }
    
    public function signOff (Request $request)
    {

        $event = new SlackEvent ($request);
        $bot   = new SlackClient ();

        $user = User::where ("slack_id", "=", $event->userId)
            ->first ();

        $eaters = Eater::where ("user_id", "=", $user->id)
            ->whereDate ("created_at", Carbon::today ())
            ->get ();

        foreach ($eaters as $eater)
            $eater->delete ();

        $bot->sendEphemeralMessageToChannel (env ("SLACK_BOT_CHANNEL"), "@$user->username jammer dat je laat afzien van de lunch, je bent uitgeschreven!", $user->slack_id);
        $bot->sendMessageToChannel (env ("SLACK_BOT_SURVEILLANCE_CHANNEL"), "@$user->username heeft zich uitgeschreven van de lunch.");

    }

}