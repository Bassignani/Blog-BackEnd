<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\ApiAuthMiddleware;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
Route::get('/test-orm', 'PruebaController@testOrm');

//RUTAS DE API
Route::get('/usuario/pruebas', 'UserController@pruebas');
Route::get('/entrada/pruebas', 'PostController@pruebas');
Route::get('/categoria/pruebas', 'CategoryController@pruebas');

//RUTAS DEL CONTROLADOR DE USUARIOS
Route::post('/api/register', 'UserController@register');
Route::post('/api/login', 'UserController@login');
Route::put('/api/user/update', 'UserController@update');
Route::post('/api/user/upload', 'UserController@upload')->middleware(ApiAuthMiddleware::class);
Route::get('/api/user/avatar/{filename}', 'UserController@getImage');
Route::get('/api/user/detail/{id}', 'UserController@detail');

//RUTAS DEL CONTROLADOR DE CATEGORIAS
Route::resource('/api/category', 'CategoryController');  //me define automaticamente las rutas y los metodos que debo usar en el controlador.
                                                        // Los cuales lo puedo ver desde la consola con-> php artisan route:list

//RUTAS DEL CONTROLADOR DE POST
Route::resource('/api/post', 'PostController');
Route::post('/api/post/upload', 'PostController@upload'); //Ya esta protegida por el middleware ya que no esta especificada entre las exepciones en el __construct de el controlador
Route::get('/api/post/image/{filename}', 'PostController@getImage');
Route::get('/api/post/category/{id}', 'PostController@getPostsByCategory');
Route::get('/api/post/user/{id}', 'PostController@getPostsByUser');
