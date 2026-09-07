<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    header('Location: /index.php');
    exit;
});

Route::get('/booking', function () {
    header('Location: /booking.php');
    exit;
});

Route::get('/aboutus', function () {
    header('Location: /aboutus.php');
    exit;
});

Route::get('/gallery', function () {
    header('Location: /gallery.php');
    exit;
});

Route::get('/tournament', function () {
    header('Location: /tournament.php');
    exit;
});
