<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\WebController;
use App\Http\Controllers\MarkerController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\RuasJalanController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route::get('/', [WebController::class, 'index']);

Route::get('/', [App\Http\Controllers\WebController::class, 'index']);

Auth::routes();

// Route::get('/home', [HomeController::class, 'index'])->name('home');

// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home')->middleware('check.token');

Route::get('/home', [App\Http\Controllers\UserController::class, 'index'])->middleware('check.token')->name('home');

// Route::get('/map', [App\Http\Controllers\RegionController::class, 'index'])->middleware('check.token')->name('map.show');

// Route untuk peta dan marker
Route::get('/map', [MarkerController::class, 'showMap'])->name('map.show');
Route::get('/marker/add', [MarkerController::class, 'create'])->name('marker.add');
Route::post('/marker/store', [MarkerController::class, 'store'])->name('marker.store');
Route::get('/marker/edit/{id}', [MarkerController::class, 'edit'])->name('marker.edit');
Route::put('/marker/update/{id}', [MarkerController::class, 'update'])->name('marker.update');
Route::delete('/marker/delete/{id}', [MarkerController::class, 'destroy'])->name('marker.delete');
Route::post('/marker/updatePosition/{id}', [MarkerController::class, 'updatePosition'])->name('marker.updatePosition');
Route::get('/marker/get-all', [MarkerController::class, 'getAllMarkers'])->name('marker.getAll');

//routes untuk add ruasjalan
Route::get('/test-api', [RuasJalanController::class, 'testApi'])->name('test.api');
Route::get('/ruasjalan/add', [RuasJalanController::class, 'create'])->name('ruasjalan.add');
Route::post('/ruasjalan/store', [RuasJalanController::class, 'store'])->name('ruasjalan.store');
// Route::get('/ruasjalan/all', [RuasJalanController::class, 'getAll'])->name('ruasjalan.getAll');
Route::get('/ruasjalan/create', [RuasJalanController::class, 'create'])->name('ruasjalan.create');
Route::get('/ruas-jalan/tabel', [RuasJalanController::class, 'tabel'])->name('ruasjalan.tabel');

// Route untuk debugging ruas jalan
Route::get('/debug-ruasjalan', [RuasJalanController::class, 'debugRuas'])->name('ruasjalan.debug');
Route::get('/ruasjalan/getall', [RuasJalanController::class, 'getAll'])->name('ruasjalan.getAll');
