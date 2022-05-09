<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\BgImageController;
use App\Http\Controllers\Api\ReactionController;
use App\Http\Controllers\Api\CommentController;
use App\Models\Post;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::middleware(['api', 'auth:api'])->group(function () {
    Route::post('login', [AuthController::class, 'login'])->name('auth.login')->withoutMiddleware('auth:api');
    Route::get('profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/posts', [PostController::class, 'index'])->name('reactions.index');
    Route::get('/posts/personal', [PostController::class, 'indexPersonal'])->name('reactions.indexPersonal');
    Route::post('/reactions', [ReactionController::class, 'index'])->name('reactions.index');
    Route::post('/reaction', [ReactionController::class, 'store'])->name('reactions.store');
    Route::get('/comments/{id}', [CommentController::class, 'index'])->name('comments.index');
    Route::post('/upload', [PostController::class, 'upload'])->name('upload');
    Route::get('/file', [PostController::class, 'getFile'])->name('file');
    Route::get('/bgImage', [BgImageController::class, 'index'])->name('bgImage.index');
    Route::post('/post', [PostController::class, 'store'])->name('postStore');
    Route::post('/postUpdate', [PostController::class, 'update'])->name('postUpdate');


});
