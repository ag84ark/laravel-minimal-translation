<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => config('laravel-minimal-translation.route_middleware'), 'prefix' => config('laravel-minimal-translation.route') ], function (){
    Route::get("/{lang?}",
        [\Ag84ark\LaravelMinimalTranslation\Http\Controllers\MinimalTranslationController::class, 'index'])
        ->name('minimal_translation.index');

    Route::post( "/{lang?}",
        [\Ag84ark\LaravelMinimalTranslation\Http\Controllers\MinimalTranslationController::class, 'save'])
        ->name('minimal_translation.save');
});




