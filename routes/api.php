<?php

use Illuminate\Support\Facades\Route;

// Şimdilik boş, her gün dolacak
Route::get('/test', function () {
    return response()->json(['message' => 'API çalışıyor!']);
});
