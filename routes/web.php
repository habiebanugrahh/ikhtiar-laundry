<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $dbConnected = false;
    $dbName = null;
    $dbError = null;

    try {
        DB::connection()->getPdo();
        $dbConnected = true;
        $dbName = DB::connection()->getDatabaseName();
    } catch (\Throwable $e) {
        $dbError = $e->getMessage();
    }

    return view('setup-check', compact('dbConnected', 'dbName', 'dbError'));
});