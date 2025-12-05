<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\FilmController;
use App\Http\Controllers\StockController;

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

    Route::get('/stocks', [StockController::class, 'list'])->name('stocks.index');
    Route::get('/stocks/summary', [StockController::class, 'summary'])->name('stocks.summary');
    Route::get('/stocks/create', [StockController::class, 'create'])->name('stocks.create');
    Route::post('/stocks', [StockController::class, 'store'])->name('stocks.store');

    // Routes avec paramètres {id} après
    Route::get('/films/{id}/edit', [FilmController::class, 'edit'])->name('films.edit');
    Route::put('/films/{id}', [FilmController::class, 'update'])->name('films.update');
    Route::delete('/films/{id}', [FilmController::class, 'destroy'])->name('films.destroy');
    Route::get('/films/{id}', [FilmController::class, 'show'])->name('films.show');

    Route::get('/stocks/{id}/edit', [StockController::class, 'edit'])->name('stocks.edit');
    Route::put('/stocks/{id}', [StockController::class, 'update'])->name('stocks.update');
    Route::delete('/stocks/{id}', [StockController::class, 'destroy'])->name('stocks.destroy');
    Route::get('/stocks/{id}/rentals', [StockController::class, 'getRentals'])->name('stocks.rentals');
    Route::get('/stocks/{id}/availability', [StockController::class, 'checkAvailability'])->name('stocks.availability');
    Route::post('/stocks/availability/batch', [StockController::class, 'checkAvailabilityBatch'])->name('stocks.availability.batch');
});
