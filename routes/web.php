<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group (["prefix" => "/slack"], function () use ($router)
{

    $router->post ("/register",      "UserController@registerSlackUser"); //implemented
    $router->post ("/deposit",       "TransactionController@deposit"); //implemented
    $router->post ("/happyaccident", "TransactionController@happyAccident"); //implemented
    $router->post ("/balances",      "BalanceController@getAll"); // implemented
    $router->post ("/transactions",  "TransactionController@getAllRecent"); // Implemented
    $router->post ("/hungry",        "LunchController@signUp"); // Implemented
    $router->post ("/overview",      "LunchController@overview"); // Implemented
    $router->post ("/nothungry",     "LunchController@signOff");

});
