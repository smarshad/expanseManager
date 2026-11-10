<?php

// routes/api.php

use App\Http\Controllers\RoleController;
use Illuminate\Support\Facades\Route;

// routes/api.php


Route::get('/users', function () {
    return response()->json([
        'id' => 1,
        'name' => 'Test User',
    ]);
});


