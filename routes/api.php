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
use App\Http\Controllers\Api\MemberController;
use App\Http\Controllers\Api\UserController;


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
    Route::post('/reactions', [ReactionController::class, 'index'])->name('reactions.index');
    Route::post('/reaction', [ReactionController::class, 'store'])->name('reactions.store');

    Route::post('/comments', [CommentController::class, 'index'])->name('comments.index');
    Route::post('/comment', [CommentController::class, 'store'])->name('commentStore');
    Route::post('/comments/{id}', [CommentController::class, 'update'])->name('Comment.update');
    Route::post('/comment/destroy/{id}', [CommentController::class, 'destroy'])->name('Comment.destroy');

    Route::get('/bgImage', [BgImageController::class, 'index'])->name('bgImage.index');

    Route::get('/posts/profile', [PostController::class, 'getProfile'])->name('post.profile');
    Route::get('/posts', [PostController::class, 'index'])->name('post.index');
    Route::get('/posts/group', [PostController::class, 'listPostGroup'])->name('post.groups');
    Route::get('/posts/group/{id}', [PostController::class, 'listPostByGroup'])->name('post.listPostByGroup');
    Route::get('/posts/personal', [PostController::class, 'indexPersonal'])->name('reactions.indexPersonal');
    Route::get('/posts/byPerson/{id}', [PostController::class, 'indexByPerson'])->name('reactions.indexByPerson');
    Route::get('/posts/group/{id}', [PostController::class, 'indexGroup'])->name('reactions.indexGroup');
    Route::post('/posts', [PostController::class, 'store'])->name('post.store');
    Route::post('/posts/{id}', [PostController::class, 'destroy'])->name('post.destroy');
    Route::post('/post/{id}', [PostController::class, 'update'])->name('post.update');

    Route::get('/relations/index/{id}', [RelationshipController::class, 'index'])->name('relationship.index');
    Route::get('/relations/check/{id}', [RelationshipController::class, 'check'])->name('relationship.check');
    Route::get('/relations/destroy/{id}', [RelationshipController::class, 'destroy'])->name('relationship.destroy');
    Route::get('/relations/accept/{id}', [RelationshipController::class, 'accept'])->name('relationship.accept');
    Route::get('/relations/send/{id}', [RelationshipController::class, 'sendRequest'])->name('relationship.send');
    Route::post('/relations/res/{id}', [RelationshipController::class, 'response'])->name('relationship.response');
    Route::get('/relations/address', [RelationshipController::class, 'listFriendByAddress'])->name('relationship.listFriendByAddress');
    // Route::get('/relations/friend', [RelationshipController::class, 'listFriend'])->name('relationship.listFriend');
    Route::get('/relations/birth', [RelationshipController::class, 'listFriendByBirthday'])->name('relationship.listFriendByBirthday');
    Route::get('/relation/listFriend', [RelationshipController::class, 'listFriend'])->name('relationship.listFriend');
    Route::get('/relation/listSend', [RelationshipController::class, 'listSend'])->name('relationship.listSend');
    Route::get('/relations/listRequest', [RelationshipController::class, 'listRequest'])->name('relationship.listRequest');
    Route::get('/relations/listSuggest', [RelationshipController::class, 'listSuggest'])->name('relationship.listSuggest');


    Route::get('/profiles', [ProfileController::class, 'index'])->name('profile.index');
    Route::get('/profiles/list', [ProfileController::class, 'list'])->name('profile.list');
    Route::post('/profiles', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/groups', [GroupController::class, 'list'])->name('group.list');
    Route::get('/groups/show/{id}', [GroupController::class, 'show'])->name('group.show');
    Route::get('/groups/me', [GroupController::class, 'listOfMe'])->name('group.listOfMe');
    Route::get('/groups/mec', [GroupController::class, 'listOfMeCurrent'])->name('group.listOfMeCurrent');
    Route::get('/groups/send', [GroupController::class, 'listSend'])->name('group.Send');
    Route::post('/groups', [GroupController::class, 'store'])->name('group.store');
    Route::get('/groups/join/{id}', [GroupController::class, 'joinGroup'])->name('group.join');
    Route::get('/groups/member/{id}', [GroupController::class, 'listMember'])->name('group.member');
    Route::get('/groups/join', [GroupController::class, 'listJoin'])->name('join');

    Route::get('members/index/{id}', [MemberController::class, 'listMember'])->name('member.listMember');
    Route::get('members/request/{id}', [MemberController::class, 'listRequest'])->name('member.listRequest');
    Route::post('members/destroy', [MemberController::class, 'destroy'])->name('member.destroy');
    Route::post('members/accept', [MemberController::class, 'accept'])->name('member.accept');


    Route::get('users/getImages/{id}', [UserController::class, 'getImages'])->name('member.getImages');
    Route::get('users/getVideos/{id}', [UserController::class, 'getVideos'])->name('member.getVideos');
});
