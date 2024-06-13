<?php

use Illuminate\Support\Facades\Route;
use Khairulkabir\BkashPay\Http\Controllers\BkashPayController;

Route::group(['controller' => BkashPayController::class, 'middleware' => ['web', 'core']], function () {
    Route::get('payment/bkashpay/callback', 'getCallback')->name('payments.bkashpay.callback');
});
