<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\App\PostController;
use App\Http\Controllers\App\HomeController;
use App\Http\Controllers\App\RelationController;
use App\Http\Controllers\App\SettingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\App\ReactionController;
use App\Http\Controllers\NoticesController;
use App\Http\Controllers\Api\RelationshipController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'authenticate'])->name('auth.authenticate');
Route::get('/register', [AuthController::class, 'register'])->name('auth.register');
Route::post('/register', [AuthController::class, 'registerAccount'])->name('auth.register_account');
Route::get('/error', function () {
    return view('app.error');
})->name('error');

Route::middleware('auth')->group(function () {
    Route::get('/logout', [AuthController::class, 'logout'])->name('auth.logout');
    Route::get('/', [HomeController::class, 'index'])->name('home.index');

    Route::get('/email/verify', [VerificationController::class, 'notice'])->name('verification.notice');
    Route::post('/email/resend', [VerificationController::class, 'resend'])->name('verification.resend');
    Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])->name('verification.verify');

    Route::resource('posts.comments', CommentController::class)->middleware('verified');

    Route::get('/profile', [ProfileController::class, 'getProfile'])->name('profile.get_profile');
    Route::put('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('profile/upload', [ ProfileController::class, 'upload' ])->name('profile.upload');

    // Route::get('/relations', [RelationController::class, 'getAddFriendList'])->name('relations.get_add_friend_list');
    Route::post('/relations/{relation}', [RelationController::class, 'addFriend'])->name('relations.add_friend');
    Route::get('/relations/requests', [RelationController::class, 'getRequests'])->name('relations.get_requests');
    Route::patch('/relations/{relation}', [RelationController::class, 'responseRequest'])->name('relations.response_request');

    Route::get('/posts/friends', [PostController::class, 'getFriendPosts'])->name('post.get_friend_posts');
    Route::resource('posts', PostController::class);


    Route::get('/relations/myfriend', [RelationController::class, 'getMyFriends'])->name('relations.myfriend');
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::patch('/settings', [SettingController::class, 'changeSettings'])->name('settings.change_settings');
    Route::resource('reactions', ReactionController::class);
    Route::resource('notices', NoticesController::class);

});

Route::get('/relations', [RelationshipController::class, 'index'])->name('relationsmyfriend');
