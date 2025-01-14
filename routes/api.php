<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\HeroSectionController;
use App\Http\Controllers\PostController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\UsersController;
use App\Services\UserActivityService;

/**
 * PUBLIC ROUTES
 */
//Auth
Route::post('login', [RegisterController::class, "login"])->name("user.login");
Route::post('register', [RegisterController::class, "register"])->name("user.register");

Route::get("access", [UserActivityService::class, 'ping'])->name("access.home");

//Post Section
Route::get("post/get", [PostController::class, "get"])->name("get.posts");
Route::get("post/get-by-slug/{slug}", [PostController::class, "getBySlug"])->name("get.posts.slug");
Route::get("post/get-post-by-category/{slug}", [PostController::class, "getArticlesByCat"])->name("get.posts.by.category");


/**
 * AUTH ROUTES
 */
Route::middleware('auth:sanctum')->group(function () {
    //Auth

    Route::get('logout', [RegisterController::class, "logout"])->name("user.logout");

    //Users
    Route::get('users', [UsersController::class, 'index'])->name('get.users');
    Route::post('users/update', [UsersController::class, 'update'])->name('update.users');
    Route::post('users/reset-password', [UsersController::class, 'resetPassword'])->name('reset.password.users');
    Route::delete('users/delete/{id}', [UsersController::class, 'delete'])->name('delete.users');

    //Post
    Route::group(['prefix' => 'post'], static function () {
        Route::get("get-all", [PostController::class, "getAllToDash"])->name("get.all.posts");
        Route::post("create", [PostController::class, "create"])->name("create.post");
        Route::post("update/{id}", [PostController::class, "update"])->name("update.post");
        Route::delete("delete/{id}", [PostController::class, "delete"])->name("delete.post");
    });

    //Categories
    Route::group(['prefix' => 'categories'], static function () {
        Route::get("", [CategoryController::class, "index"])->name("get.categories");
        Route::get("by-id/{category_id}", [CategoryController::class, "show"])->name("get.by-id.categories");
        Route::post("create", [CategoryController::class, "create"])->name("create.category");
        Route::put("update/{category_id}", [CategoryController::class, "update"])->name("update.category");
        Route::delete("delete/{category_id}", [CategoryController::class, "destroy"])->name("delete.category");
    });
});
