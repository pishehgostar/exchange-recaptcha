<?php

use Illuminate\Support\Facades\Route;

Route::group([
        'middleware'=>'api',
        'prefix'=>'api'
    ],function (){
        require_once 'api.php';
    });

Route::group([
        'middleware'=>'web'
    ],function (){
        require_once 'web.php';
    });
