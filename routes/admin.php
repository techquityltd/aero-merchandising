<?php
use Aero\Merchandising\Http\Controllers\MerchandisingController;

Route::get('merchandising', [MerchandisingController::class, 'index'])->name('admin.modules.merchandising.index');
Route::post('merchandising', [MerchandisingController::class, 'index'])->name('admin.modules.merchandising.index');

Route::get('merchandising/listings', [MerchandisingController::class, 'listings'])->name('admin.modules.merchandising.listings');
Route::post('merchandising/store', [MerchandisingController::class, 'store'])->name('admin.modules.merchandising.store');
