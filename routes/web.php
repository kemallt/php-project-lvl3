<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/', 'App\Http\Controllers\UrlController@create')->name('urls.create');
Route::get('/urls', 'App\Http\Controllers\UrlController@index')->name('urls.index');
Route::get('/urls/{url}', 'App\Http\Controllers\UrlController@show')->name('urls.show');
Route::post('/urls', 'App\Http\Controllers\UrlController@store')->name('urls.store');

Route::post('/urls/{url}/checks', 'App\Http\Controllers\UrlController@check')->name('urls.checks');
