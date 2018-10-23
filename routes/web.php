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

    $router->post ("/register",      "UserController@registerSlackUser");
    $router->post ("/deposit",       "TransactionController@deposit");
    $router->post ("/happyaccident", "TransactionController@happyAccident");
    $router->post ("/balances",      "BalanceController@getAll");
    $router->post ("/transactions",  "TransactionController@getAllRecent");
    $router->post ("/hungry",        "LunchController@signUp");
    $router->post ("/overview",      "LunchController@overview");
    $router->post ("/budget",        "LunchController@budget");

});
