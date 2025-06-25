<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Route;
// use App\Http\Controllers\Web\AuthController;


// Home
// Route::get('', [HomeController::class, 'index']);
// Route::get('home', [HomeController::class, 'index']);
// Route::post('home', [HomeController::class, 'search'])->name('navbar-search');

// Authentification
// Route::get('auth/register', [AuthController::class, 'register']);
// Route::post('auth/register', [AuthController::class, 'registerSubmit'])->name('register-submit');

// Route::get('auth/login', [AuthController::class, 'login']);
// Route::post('auth/login', [AuthController::class, 'loginSubmit'])->name('login-submit');

// Route::get('auth/forgot-password', [AuthController::class, 'forgotPassword'])->name('forgot-password');
// Route::get('auth/forgot-password', [AuthController::class, 'forgotPasswordSubmit'])->name('forgot-password-submit');

//Route::get('auth/logout', [AuthController::class, 'logout'])->name('auth-logout');

// User
// Route::get('user/change-password', [UserController::class, 'changePassword']);
// Route::post('user/change-password', [UserController::class, 'changePasswordSubmit'])->name('change-password-submit');

// Route::get('user/change-email', [UserController::class, 'changeEmail']);
// Route::post('user/change-email', [UserController::class, 'changeEmailSubmit'])->name('change-email-submit');

// Route::get('user/view/{id}', [UserController::class, 'show']);

// Route::get('user/delete', [AuController::class, 'show']);

// Contact
// Route::get('contact', [ContactController::class, 'show']);
// Route::post('contact', [ContactController::class, 'submit'])->name('contact-submit');

// Donation
// Route::get('donation', [DonationController::class, 'index']);
// Route::get('donation', [DonationController::class, 'submit'])->name('donation-submit');