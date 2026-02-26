<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\NewsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

Route::get('/', [NewsController::class, 'index'])->name('home');
Route::get('/news/{news}', [NewsController::class, 'show'])->name('news.show');

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
    Route::get('/dashboard', [NewsController::class, 'dashboard'])->name('dashboard');
});

require __DIR__ . '/auth.php';
