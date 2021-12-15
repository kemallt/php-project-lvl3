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

Route::resource('/urls', 'App\Http\Controllers\UrlController')->only(['create', 'index', 'show', 'store']);
Route::get('/', 'App\Http\Controllers\UrlController@create')->name('urls.main');

Route::post('/urls/{url}/checks', 'App\Http\Controllers\UrlCheckController@check')->name('urls.checks');
