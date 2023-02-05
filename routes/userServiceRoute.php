<?php
/** @var \Laravel\Lumen\Routing\Router $router */

use App\Http\Controllers\UserServiceController;
use Illuminate\Http\Request;

//products
//,'client.credentials'
$router->group(['prefix' => 'api/v1/users', 'middleware' => ['cors', 'jsonResponse', 'checkApp', 'auth']], function () use ($router) {
    $router->get('', 'UserServiceController@index');
    $router->get('/{any:.*}', 'UserServiceController@show');
    $router->post('/{any:.*}', 'UserServiceController@store');
    $router->put('/{any:.*}', 'UserServiceController@update');
    $router->patch('/{any:.*}', 'UserServiceController@update');
    $router->delete('/{any:.*}', 'UserServiceController@destroy');
});


