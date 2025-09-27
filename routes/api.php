<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\NewsSourceController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\NewsAggregatorController;
use App\Http\Controllers\Api\UserPreferenceController;
use App\Http\Controllers\Api\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

// Public authentication routes with strict rate limiting
Route::middleware(['throttle:5,1'])->prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Protected authentication routes with moderate rate limiting
Route::middleware(['api.token', 'throttle:20,1'])->prefix('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
});

/*
|--------------------------------------------------------------------------
| News Aggregator API Routes
|--------------------------------------------------------------------------
*/

// News Aggregator Management with moderate rate limiting
Route::middleware(['throttle:30,1'])->prefix('aggregator')->group(function () {
    Route::post('/aggregate', [NewsAggregatorController::class, 'aggregate']);
    Route::get('/statistics', [NewsAggregatorController::class, 'statistics']);
    Route::get('/dashboard', [NewsAggregatorController::class, 'dashboard']);
});

// Articles with generous rate limiting for public access
Route::middleware(['throttle:100,1'])->prefix('articles')->group(function () {
    Route::get('/', [ArticleController::class, 'index']);
    Route::get('/featured', [ArticleController::class, 'featured']);
    Route::get('/latest', [ArticleController::class, 'latest']);
    Route::get('/search', [ArticleController::class, 'search']);
    Route::get('/category/{categoryId}', [ArticleController::class, 'byCategory']);
    Route::get('/source/{sourceId}', [ArticleController::class, 'bySource']);
    Route::get('/{id}', [ArticleController::class, 'show']);
});

// News Sources with moderate rate limiting
Route::middleware(['throttle:60,1'])->prefix('sources')->group(function () {
    Route::get('/', [NewsSourceController::class, 'index']);
    Route::get('/active', [NewsSourceController::class, 'active']);
    Route::get('/statistics', [NewsSourceController::class, 'statistics']);
    Route::get('/{id}', [NewsSourceController::class, 'show']);
});

// Categories with moderate rate limiting
Route::middleware(['throttle:60,1'])->prefix('categories')->group(function () {
    Route::get('/', [CategoryController::class, 'index']);
    Route::get('/active', [CategoryController::class, 'active']);
    Route::get('/statistics', [CategoryController::class, 'statistics']);
    Route::get('/{id}', [CategoryController::class, 'show']);
});

// User Preferences (requires authentication) with moderate rate limiting
Route::middleware(['api.token', 'throttle:50,1'])->prefix('user')->group(function () {
    Route::get('/preferences', [UserPreferenceController::class, 'index']);
    Route::put('/preferences', [UserPreferenceController::class, 'update']);
    Route::post('/preferences/sources', [UserPreferenceController::class, 'addPreferredSource']);
    Route::delete('/preferences/sources', [UserPreferenceController::class, 'removePreferredSource']);
    Route::post('/preferences/categories', [UserPreferenceController::class, 'addPreferredCategory']);
    Route::delete('/preferences/categories', [UserPreferenceController::class, 'removePreferredCategory']);
    Route::get('/personalized-articles', [UserPreferenceController::class, 'personalizedArticles']);
});
