<?php

namespace Khairulkabir\BkashPay;

use Botble\PluginManagement\Abstracts\PluginOperationAbstract;
use Botble\Setting\Models\Setting;

class Plugin extends PluginOperationAbstract
{
    public static function remove(): void
    {

        Setting::query()
            ->whereIn('key', [
                'payment_bkashpay_name',
                'payment_bkashpay_description',
                'payment_bkashpay_username',
                'payment_bkashpay_password ',
                'payment_bkashpay_appKey',
                'payment_bkashpay_appSecretKey',
                'payment_bkashpay_mode',
            ])
            ->delete();
    }
}
