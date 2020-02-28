<?php

use Illuminate\Support\Facades\Route;

Route::prefix('authorization')->group(function () {
    Route::get('/viewAnything', 'AuthorizeController@viewAnything')->name('authorization.view-anything');
    Route::get('/viewAnyPost', 'AuthorizeController@viewAnyPost')->name('authorization.view-any-post');
    Route::get('/viewPost/{post_id}', 'AuthorizeController@viewPost')->name('authorization.view-post');
});
