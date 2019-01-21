<?php namespace App\Http\Controllers;

use App\User;
use App\Eater;
use Carbon\Carbon;
use App\SlackEvent;
use App\SlackClient;
use App\Transaction;
use App\UserBalance;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Http\Controllers\Controller;

class LunchController extends Controller
{

    private $responseString;

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

        // $deadline = Carbon::createFromFormat ("H:i", env ("SLACK_BOT_CLOSING_TIME"));
        // if (Carbon::now ()->gt ($deadline))
        // {

        //     $bot->sendEphemeralMessageToChannel (env ("SLACK_BOT_CHANNEL"), " @$user->username, *Oei! Sorry*, maar je bent te laat om je in te schrijven voor de lunch. Let er op dat je je maximaal kan inschrijven tot *11:45*!", $user->slack_id);
        //     return response (null, 200);

        // }

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

        $eatersAndBalances = new Collection ();
        foreach ($eaters as $eater)
        {

            $user = User::find ($eater->user_id);
            $balance = (double) UserBalance::where ("user_id", "=", $user->id)
                ->orderBy ("created_at", "desc")
                ->first ()
                ->amount;

            $eatersAndBalances->push ((Object) ["username" => $user->username, "balance" => "$balance"]);

        }

        $eatersAndBalances->sortBy ("balance");

        $this->responseString = "";

        if ($eatersAndBalances->isEmpty ())
        {

            $this->responseString = "Vandaag eet niemand mee :(";

        } else {

            $this->responseString = "Vandaag eet mee:";

            // foreach ($eatersAndBalances->all () as $eaterWithBalance)
            $eatersAndBalances->each (function ($eaterWithBalance)
            {

                $this->responseString .= "\n@$eaterWithBalance->username | Saldo: *€$eaterWithBalance->balance*";

            });

            $budget = (double) 3 * $eaters->count ();
            $this->responseString .= "\n\nHet budget voor de lunch is: *€$budget*";

        }

        $bot->sendEphemeralMessageToChannel (env ("SLACK_BOT_CHANNEL"), $this->responseString, $event->userId);

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

        $lastEater = Eater::where ("user_id", "=", $user->id)
            ->whereDate ("created_at", Carbon::today ())
            ->first ();

        if ( ! $lastEater)
        {
            
            $bot->sendEphemeralMessageToChannel (env ("SLACK_BOT_CHANNEL"), "@$user->username Je staat niet ingeschreven voor deze lunch.", $user->slack_id);
            return response (null, 200);

        }

        $deadline = Carbon::createFromFormat ("H:i", env ("SLACK_BOT_CLOSING_TIME"));
        if (Carbon::now ()->gt ($deadline))
        {

            $bot->sendEphemeralMessageToChannel (env ("SLACK_BOT_CHANNEL"), " @$user->username, *Ja, dat kan niet*, we gaan ons niet inschrijven voor de lunch om dan op het laatste moment of na de lunch nog even uit teschrijven ;) Na *11:45* kan je je niet meer in of-uitschrijven.", $user->slack_id);
            return response (null, 200);

        }

        foreach ($eaters as $eater)
            $eater->delete ();

        $balance = UserBalance::where ("user_id", "=", $user->id)
            ->orderBy ("created_at", "desc")
            ->first ();

        $balance->amount += (double) env ("LUNCH_COST_IN_EUROS");
        $balance->save ();

        Transaction::create 
        (
            [
                "user_id"       => $user->id
            ,   "mutation_type" => "deposit"
            ,   "amount"        => (double) env ("LUNCH_COST_IN_EUROS")
            ]
        );

        $bot->sendEphemeralMessageToChannel (env ("SLACK_BOT_CHANNEL"), "@$user->username jammer dat je laat afzien van de lunch, je bent uitgeschreven en je geld is terug gestort naar je saldo, je saldo is nu: *€$balance->amount*!", $user->slack_id);
        $bot->sendMessageToChannel (env ("SLACK_BOT_CHANNEL"), "@$user->username heeft zich uitgeschreven van de lunch :( ... Let hier op met het budget!");
        $bot->sendMessageToChannel (env ("SLACK_BOT_SURVEILLANCE_CHANNEL"), "@$user->username heeft zich uitgeschreven van de lunch.");


    }

}