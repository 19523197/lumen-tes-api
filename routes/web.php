<?php

/** @var \Laravel\Lumen\Routing\Router $router */

use App\Http\Controllers\AuthController;

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

//ROUTES BOOKS
$router->group(['prefix' => '/books'], function () use ($router) {
    $router->get('/', 'Books\BooksController@index');
    $router->get('/{id}', 'Books\BooksController@show');
    $router->post('', 'Books\BooksController@create');
    $router->put('/{id}', 'Books\BooksController@update');
    $router->delete('/{id}', 'Books\BooksController@destroy');
});


//ROUTES USER
$router->group(['prefix' => '/user'], function () use ($router) {
    $router->get('/', 'Users\UserController@index');
    $router->post('/register', 'Users\UserController@create');
    $router->post('/login', 'AuthController@login');
    $router->post('/logout', 'AuthController@logout');
});
