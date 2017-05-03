<?php

Route::get('login', 'PasswordlessController@form')->middleware('guest')->name('passwordless');
Route::post('login', 'PasswordlessController@login')->name('passwordless.login');
Route::get('auth', 'PasswordlessController@auth')->name('passwordless.auth');
