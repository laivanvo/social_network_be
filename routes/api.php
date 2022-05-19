<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\BgImageController;
use App\Http\Controllers\Api\ReactionController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\RelationshipController;
use App\Http\Controllers\Api\GroupController;


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
    Route::post('register', [AuthController::class, 'register'])->name('auth.register')->withoutMiddleware('auth:api');
    Route::get('/posts/personal', [PostController::class, 'indexPersonal'])->name('reactions.indexPersonal');
    Route::post('/reactions', [ReactionController::class, 'index'])->name('reactions.index');
    Route::post('/reaction', [ReactionController::class, 'store'])->name('reactions.store');
    Route::post('/comments', [CommentController::class, 'index'])->name('comments.index');
    Route::post('/comment', [CommentController::class, 'store'])->name('commentStore');
    Route::post('/comments/{id}', [CommentController::class, 'update'])->name('Comment.update');
    Route::get('/comments/{id}', [CommentController::class, 'destroy'])->name('Comment.destroy');
    Route::get('/bgImage', [BgImageController::class, 'index'])->name('bgImage.index');
    Route::get('/posts', [PostController::class, 'index'])->name('post.index');
    Route::post('/posts', [PostController::class, 'store'])->name('post.store');
    Route::post('/posts/{id}', [PostController::class, 'destroy'])->name('post.destroy');
    Route::post('/post/{id}', [PostController::class, 'update'])->name('post.update');
    Route::get('/relations/index/{id}', [RelationshipController::class, 'index'])->name('relationship.index');
    Route::post('/relations/send/{id}', [RelationshipController::class, 'sendRequest'])->name('relationship.send');
    Route::post('/relations/res/{id}', [RelationshipController::class, 'response'])->name('relationship.response');
    Route::get('/relations', [RelationshipController::class, 'listRequest'])->name('relationship.list');
    Route::get('/relations/address', [RelationshipController::class, 'listFriendByAddress'])->name('relationship.listFriendByAddress');
    Route::get('/relations/friend', [RelationshipController::class, 'listFriend'])->name('relationship.listFriend');
    Route::get('/relations/birth', [RelationshipController::class, 'listFriendByBirthday'])->name('relationship.listFriendByBirthday');
    Route::get('/profiles', [ProfileController::class, 'index'])->name('profile.index');
    Route::post('/profiles', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/groups', [GroupController::class, 'index'])->name('group.index');
});
