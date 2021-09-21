<?php

use App\SirenaFacade;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebhooksController;

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

Route::get('/', function () {
    //dd(SirenaFacade::getApikey());
    return view('welcome');
});

Route::post('/webhooks',WebhooksController::class);

Route::webhooks('webhook-receiving-url');
