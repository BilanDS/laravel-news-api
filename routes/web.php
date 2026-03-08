<?php

use App\Http\Controllers\Dashboard\NewsController as DashboardNewsController;
use App\Http\Controllers\Web\NewsController as WebNewsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', [WebNewsController::class, 'index'])->name('home');
Route::get('/news/{news}', [WebNewsController::class, 'show'])->name('news.show');

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login-web', function (Request $request) {
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    return back()->withErrors(['email' => 'Невірний email або пароль']);
})->name('login.web');

Route::post('/logout-web', function (Request $request) {
    Auth::guard('web')->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/');
})->name('logout.web');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardNewsController::class, 'index'])->name('dashboard');
});

require __DIR__.'/auth.php';
