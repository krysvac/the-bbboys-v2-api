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
    return response(view("errors.404"), 404);
});

$router->post('login', function () use ($router) {
    return response(view("errors.400"), 400);
});

$router->get('logout', function () use ($router) {
    return response(view("errors.400"), 400);
});

$router->group(['prefix' => 'api'], function () use ($router) {
    $router->post('vote', ['uses' => 'ApiController@test']);

    $router->post('changepassword', ['uses' => 'ApiController@test']);

    $router->post('createLink', ['uses' => 'ApiController@test']);

    $router->post('register', ['uses' => 'ApiController@test']);

    $router->get('votes', ['uses' => 'ApiController@test']);

    $router->group(['prefix' => 'food'], function () use ($router) {
        $router->get('bistroj', ['uses' => 'ApiController@test']);
        $router->get('villa', ['uses' => 'ApiController@test']);
    });
});
