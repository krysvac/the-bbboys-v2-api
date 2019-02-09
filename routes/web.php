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
    throw new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException();
});

$router->post('login', ['uses' => 'AuthController@authenticate']);

$router->get('validateAuth', ['middleware' => 'jwt.authUser',
    'uses' => 'AuthController@validateToken']);

$router->group(['prefix' => 'api'], function () use ($router) {
    $router->get('/', function () use ($router) {
        throw new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException();
    });

    $router->get('poll/{poll_id}', ['uses' => 'ApiController@getPoll']);

    $router->get('poll/{poll_id}/choices', ['uses' => 'ApiController@getPollChoices']);

    $router->get('votes/{poll_id}', ['uses' => 'ApiController@getPollAnswers']);

    $router->group(['prefix' => 'food'], function () use ($router) {
        $router->get('bistroj', ['uses' => 'ApiController@getBistrojItems']);
        $router->get('villa', ['uses' => 'ApiController@getVillaItems']);
    });
});

$router->group(['prefix' => 'api', 'middleware' => 'jwt.authUser'], function () use ($router) {
    $router->post('vote', ['uses' => 'ApiController@vote']);

    $router->post('changepassword', ['uses' => 'ApiController@changePassword']);
});

$router->group(['prefix' => 'api', 'middleware' => 'jwt.authAdmin'], function () use ($router) {
    $router->post('createLink', ['uses' => 'ApiController@createLink']);

    $router->get('registrationLinks', ['uses' => 'ApiController@getRegistrationLinks']);
});
