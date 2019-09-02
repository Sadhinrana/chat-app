<?php

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

/*Route::get('/', function () {
    return view('welcome');
});*/

Auth::routes(['verify' => true]);

//Route::get('/home', 'HomeController@index')->name('home');

Route::get('users', 'UserController@index')->name('users.index');

Route::get('messages/{receiver_id}', 'MessageController@index')->name('messages.index');
Route::put('messages/{message}', 'MessageController@update')->name('messages.update');
Route::delete('messages/{message}', 'MessageController@destroy')->name('messages.destroy');
Route::post('messages', 'MessageController@store')->name('messages.store');
Route::post('messages/image', 'MessageController@imageStore')->name('messages.store.image');

Route::get( '/{vue_route?}', 'HomeController@index' )->where( 'vue_route', '(.*)' );