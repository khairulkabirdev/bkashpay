<?php

namespace Khairulkabir\BkashPay\Providers;

use Botble\Base\Facades\Html;
use Botble\Payment\Enums\PaymentMethodEnum;
use Botble\Payment\Facades\PaymentMethods;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Khairulkabir\BkashPay\Forms\BkashPaymentMethodForm;
use Khairulkabir\BkashPay\Services\Gateways\BkashPayPaymentService;

class HookServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        add_filter(PAYMENT_FILTER_ADDITIONAL_PAYMENT_METHODS, [$this, 'registerBkashPayMethod'], 2, 2);

        $this->app->booted(function () {
            add_filter(PAYMENT_FILTER_AFTER_POST_CHECKOUT, [$this, 'checkoutWithBkashPay'], 2, 2);
        });

        add_filter(PAYMENT_METHODS_SETTINGS_PAGE, [$this, 'addPaymentSettings'], 2);

        add_filter(BASE_FILTER_ENUM_ARRAY, function ($values, $class) {
            if ($class == PaymentMethodEnum::class) {
                $values['BKASHPAY'] = BKASHPAY_PAYMENT_METHOD_NAME
                ;
            }

            return $values;
        }, 2, 2);

        add_filter(BASE_FILTER_ENUM_LABEL, function ($value, $class) {
            if ($class == PaymentMethodEnum::class && $value == BKASHPAY_PAYMENT_METHOD_NAME
            ) {
                $value = 'BkashPay';
            }

            return $value;
        }, 2, 2);

        add_filter(BASE_FILTER_ENUM_HTML, function ($value, $class) {
            if ($class == PaymentMethodEnum::class && $value == BKASHPAY_PAYMENT_METHOD_NAME
            ) {
                $value = Html::tag(
                    'span',
                    PaymentMethodEnum::getLabel($value),
                    ['class' => 'label-success status-label']
                )
                    ->toHtml();
            }

            return $value;
        }, 2, 2);

        add_filter(PAYMENT_FILTER_GET_SERVICE_CLASS, function ($data, $value) {
            if ($value == BKASHPAY_PAYMENT_METHOD_NAME
            ) {
                $data = BkashPayPaymentService::class;
            }

            return $data;
        }, 2, 2);
    }

    public function addPaymentSettings(?string $settings): string
    {
        return $settings . BkashPaymentMethodForm::create()->renderForm();
    }

    public function registerBkashPayMethod(?string $html, array $data): string
    {
        PaymentMethods::method(BKASHPAY_PAYMENT_METHOD_NAME, [
            'html' => view('plugins/bkashpay::methods', $data)->render(),
        ]);

        return $html;
    }

    public function checkoutWithBkashPay(array $data, Request $request): array
    {
        if ($request->input('payment_method') == BKASHPAY_PAYMENT_METHOD_NAME
        ) {
            $currentCurrency = get_application_currency();

            $currencyModel = $currentCurrency->replicate();

            $bKashPayService = $this->app->make(BkashPayPaymentService::class);

            $supportedCurrencies = $bKashPayService->supportedCurrencyCodes();

            $currency = strtoupper($currentCurrency->title);

            $notSupportCurrency = false;

            if (! in_array($currency, $supportedCurrencies)) {
                $notSupportCurrency = true;

                if (! $currencyModel->where('title', 'BDT')->exists()) {
                    $data['error'] = true;
                    $data['message'] = __(":name doesn't support :currency. List of currencies supported by :name: :currencies.", [
                        'name' => 'BkashPay',
                        'currency' => $currency,
                        'currencies' => implode(', ', $supportedCurrencies),
                    ]);

                    return $data;
                }
            }

            $paymentData = apply_filters(PAYMENT_FILTER_PAYMENT_DATA, [], $request);

            if ($notSupportCurrency) {
                $usdCurrency = $currencyModel->where('title', 'BDT')->first();

                $paymentData['currency'] = 'BDT';
                if ($currentCurrency->is_default) {
                    $paymentData['amount'] = $paymentData['amount'] * $usdCurrency->exchange_rate;
                } else {
                    $paymentData['amount'] = format_price($paymentData['amount'], $currentCurrency, true);
                }
            }

            $checkoutUrl = $bKashPayService->makePayment($paymentData);

            if (isset($checkoutUrl['statusCode'])) {
                // If statusCode exists in the response
                if (isset($checkoutUrl['statusMessage'])) {
                    $data['message'] = $checkoutUrl['statusMessage'];
                } else {
                    $data['message'] = __('Something went wrong. Please try again later.');
                }
                $data['error'] = true;
            } else {
                // If no statusCode, assuming a successful response
                if ($checkoutUrl) {
                    $data['checkoutUrl'] = $checkoutUrl;
                } else {
                    $data['error'] = true;
                    $data['message'] = __('Something went wrong. Please try again later.');
                }
            }

            return $data;
        }

        return $data;
    }
}
