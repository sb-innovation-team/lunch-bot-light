<?php namespace App\Http\Controllers;

use App\User;
use App\SlackEvent;
use App\SlackClient;
use App\Transaction;
use App\UserBalance;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TransactionController extends Controller
{

    public function deposit (Request $request)
    {

        $event = new SlackEvent ($request);
        $bot   = new SlackClient ();

        $user = User::where ("slack_id", "=", $event->userId)
            ->first ();

        $userBalance = UserBalance::where ("user_id", "=", $user->id)
            ->first ();

        $depositAmount = (double) $event->text;

        $userBalance->amount += $depositAmount;
        $userBalance->save ();

        Transaction::create 
        (
            [
                "user_id"       => $user->id
            ,   "mutation_type" => "deposit"
            ,   "amount"        => $depositAmount
            ]
        );

        $bot->sendEphemeralMessageToChannel (env ("SLACK_BOT_CHANNEL"), "Je saldo is nu *€$userBalance->amount*!", $user->slack_id);

        return response (null, 200);

    }

    public function happyAccident (Request $request)
    {

        $bot = new SlackClient ();
        $bot->sendMessageToChannel (env ("SLACK_BOT_CHANNEL"), "Deze feature is nog niet beschikbaar :(");

        // $event = new SlackEvent ($request);

        // $user = User::where ("slack_id", "=", $event->userId)
        //     ->first ();

        // $userBalance = UserBalance::where ("user_id", "=", $user->id)
        //     ->first ();

        // $lastTransaction = Transaction::where ("user_id", "=", $user->id)
        //     ->orderBy ("created_at", "desc")
        //     ->first ();

        // $userBalance->amount -= $lastTransaction->amount;
        // $userBalance->save ();

        // Transaction::create 
        // (
        //     [
        //         "user_id"       => $user->id
        //     ,   "mutation_type" => "withdrawl"
        //     ,   "amount"        => $lastTransaction->amount
        //     ]
        // );
        
    }

    public function getAllRecent (Request $request)
    {

        $event = new SlackEvent ($request);

        $transactions = Transaction::all ();

        $responseString = "Transacties:\n";

        foreach ($transactions as $transaction)
        {

            $user = User::where ("id", "=", $transaction->user_id)
                ->first ();

            $transactionAmount = (double) $transaction->amount;

            $responseString .= "<@$user->slack_id> - $transaction->mutation_type : $transactionAmount\n";

        }

        $bot = new SlackClient ();
        $bot->sendMessageToChannel (env ("SLACK_BOT_CHANNEL"), $responseString);

    }
    
}