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

$router->get('poll/{poll_id}', ['uses' => 'ApiController@getPoll']);

$router->get('votes/{poll_id}', ['uses' => 'ApiController@getPollAnswers']);

$router->post('validateToken', ['uses' => 'ApiController@validateToken']);

$router->post('register', ['uses' => 'ApiController@register']);

$router->group(['prefix' => 'food'], function () use ($router) {
    $router->get('bistroj', ['uses' => 'ApiController@getBistrojItems']);
    $router->get('villa', ['uses' => 'ApiController@getVillaItems']);
});

$router->group(['middleware' => 'jwt.authUser'], function () use ($router) {
    $router->get('validateAuth', ['uses' => 'AuthController@validateToken']);

    $router->post('vote', ['uses' => 'ApiController@vote']);

    $router->post('changePassword', ['uses' => 'ApiController@changePassword']);

    $router->get('userCanVote', ['uses' => 'ApiController@getUserCanVoteToday']);

    $router->get('votingIsAllowed', ['uses' => 'ApiController@getVotingIsAllowed']);

    $router->get('selectedChoice', ['uses' => 'ApiController@getChoiceVotedFor']);

    $router->get('currentWeebChoices', ['uses' => 'ApiController@getCurrentWeebChoices']);

    $router->post('addWeebChoice', ['uses' => 'ApiController@addWeebChoice']);

    $router->get('weebVotingAllowed', ['uses' => 'ApiController@getWeebVotingIsAllowed']);

    $router->get('weebAnswersForUser', ['uses' => 'ApiController@getWeebAnswersForUser']);

    $router->get('allWeebAnswers', ['uses' => 'ApiController@getAllWeebAnswers']);

    $router->post('voteWeeb', ['uses' => 'ApiController@voteWeeb']);
});

$router->group(['middleware' => 'jwt.authAdmin'], function () use ($router) {
    $router->post('createLink', ['uses' => 'ApiController@createLink']);

    $router->get('registrationLinks', ['uses' => 'ApiController@getRegistrationLinks']);
});
