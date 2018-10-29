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
        $bot->sendMessageToChannel (env ("SLACK_BOT_SURVEILLANCE_CHANNEL"), "$user->email heeft zojuist *€$depositAmount* toegevoegd aan zijn/haar saldo. Het nieuwe saldo is *€$userBalance->amount*");

        return response (null, 200);

    }

    public function happyAccident (Request $request)
    {

        $bot = new SlackClient ();
        $event = new SlackEvent ($request);
        // $bot->sendEphemeralMessageToChannel (env ("SLACK_BOT_CHANNEL"), "Deze feature is nog niet beschikbaar :(");

        $user = User::where ("slack_id", "=", $event->userId)
            ->first ();

        $userBalance = UserBalance::where ("user_id", "=", $user->id)
            ->first ();

        $lastTransaction = Transaction::where ("user_id", "=", $user->id)
            ->orderBy ("created_at", "desc")
            ->first ();

        $correctionType;
        if ($lastTransaction->mutation_type == "deposit")
        {
            $userBalance->amount -= $lastTransaction->amount;
            $correctionType = "withdrawl";
        }
        else
        {

            $userbalance->amount += $lastTransaction->amount;
            $correctionType = "deposit";

        }
            
        $userBalance->save ();

        Transaction::create 
        (
            [
                "user_id"       => $user->id
            ,   "mutation_type" => $correctionType
            ,   "amount"        => $lastTransaction->amount
            ]
        );

        $bot->sendMessageToChannel (env ("SLACK_BOT_SURVEILLANCE_CHANNEL"), "@$user->username heeft zojuist een correctie gedaan. De transactie: *€$lastTransaction->amount* - $correctionType.");
        $bot->sendEphemeralMessageToChannel (env ("SLACK_BOT_CHANNEL"), "@$user->username Je laatste transactie is verwijderd, je saldo is nu: *€$userBalance->amount*", $user->slack_id);
        
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

            $responseString .= "@$user->username - $transaction->mutation_type : *€$transactionAmount*\n";

        }

        $bot = new SlackClient ();
        $bot->sendEphemeralMessageToChannel (env ("SLACK_BOT_CHANNEL"), $responseString, $event->userId);

    }
    
}