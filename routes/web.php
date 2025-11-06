<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\FilmController;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Routes films protégées par authentification
Route::middleware('auth')->group(function () {
    // Route de debug API
    Route::get('/debug-api', function () {
        $service = new \App\Services\ToadFilmService();
        return [
            'languages' => $service->getAllLanguages(),
            'categories' => $service->getAllCategories(),
            'actors' => $service->getAllActors()
        ];
    })->name('debug.api');

    // Liste et création (routes sans paramètres en premier)
    Route::get('/films', [FilmController::class, 'index'])->name('films.index');
    Route::get('/films/create', [FilmController::class, 'create'])->name('films.create');
    Route::post('/films', [FilmController::class, 'store'])->name('films.store');

    // Routes avec paramètres {id} après
    Route::get('/films/{id}/edit', [FilmController::class, 'edit'])->name('films.edit');
    Route::put('/films/{id}', [FilmController::class, 'update'])->name('films.update');
    Route::delete('/films/{id}', [FilmController::class, 'destroy'])->name('films.destroy');
    Route::get('/films/{id}', [FilmController::class, 'show'])->name('films.show');
});
