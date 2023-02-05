<?php

/** @var \Laravel\Lumen\Routing\Router $router */

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

$router->group(['prefix' => 'api/v1/', 'middleware' => ['cors', 'jsonResponse', 'checkApp','auth']], function () use ($router) {
    $router->post('/logout','AuthenticationController@logout');
});

$router->group(['prefix' => 'api/v1/', 'middleware' => ['cors', 'jsonResponse', 'checkApp']], function () use ($router) {
    //version
    $router->get('/', function () use ($router) {
        return $router->app->version();
    });

    //Register User
    $router->post('/users','UserServiceController@store');

    //login
    $router->post('/login','AuthenticationController@store');

    //Forgot Password / Onetime Token email verification
    $router->post('forgot-password-onetime-login','AuthenticationController@forgotPasswordOneTimeToken');
    $router->post('verify-onetime-login','AuthenticationController@verifyOnetimeToken');
    $router->post('verify-forgot-password','AuthenticationController@verifyForgotPassword');
    $router->post('reset-password','AuthenticationController@resetPassword');

    //checkAuth
    $router->post('/checkAuth','AuthenticationController@checkAuth');

});
