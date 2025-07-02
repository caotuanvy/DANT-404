<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test-env', function () {
    $myKey = env('GEMINI_API_KEY');

    return response()->json([
        'message' => 'Đây là kết quả kiểm tra đọc file .env',
        'api_key_value_read_by_laravel' => $myKey,
        'is_key_found' => !empty($myKey)
    ]);
});
