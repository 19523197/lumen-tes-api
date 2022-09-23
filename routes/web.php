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

$router->get('/', function () use ($router) {
    return $router->app->version();
});

//ROUTES BOOKS
$router->get('/books', 'Books\BooksController@index');
$router->get('/books/{id}', 'Books\BooksController@show');
$router->post('/books', 'Books\BooksController@create');
$router->put('/books/{id}', 'Books\BooksController@update');
$router->delete('/books/{id}', 'Books\BooksController@destroy');

//ROUTES USER
$router->get('/users', 'Users\UserController@index');
$router->post('/register', 'Users\UserController@create');
$router->post('/login', 'Users\UserController@login');
