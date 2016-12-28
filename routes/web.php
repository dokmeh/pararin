<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

Route::get('/', 'PagesController@home');
Route::get('aj', 'PagesController@home_ajax');
Route::get('projects', 'PagesController@projects');
Route::get('ajprojects', 'PagesController@projects_ajax');
Route::get('projects/{url}', 'PagesController@project');
Route::get('ajprojects/{url}', 'PagesController@project_ajax');
Route::get('publications', 'PagesController@publications');
Route::get('ajpublications', 'PagesController@publications_ajax');
Route::get('awards', 'PagesController@awards');
Route::get('ajawards', 'PagesController@awards_ajax');
Route::get('awards/{url}', 'PagesController@awards');
Route::get('ajawards/{url}', 'PagesController@awards_ajax');
