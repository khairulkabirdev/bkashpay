<?php

namespace Khairulkabir\BkashPay\Forms;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Forms\FieldOptions\CheckboxFieldOption;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\OnOffCheckboxField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Payment\Forms\PaymentMethodForm;

class BkashPaymentMethodForm extends PaymentMethodForm
{
    public function setup(): void
    {
        parent::setup();

        $this
            ->paymentId(BKASHPAY_PAYMENT_METHOD_NAME)
            ->paymentName('bKash')
            ->paymentDescription(__('Customer can buy product and pay with :name', ['name' => 'bkash']))
            ->paymentLogo(url('vendor/core/plugins/bkashpay/images/bkashpay.png'), )
            ->paymentUrl('https://bkash.com')
            ->paymentInstructions(view('plugins/bkashpay::settings')->render())
            ->add(
                sprintf('payment_%s_username', BKASHPAY_PAYMENT_METHOD_NAME),
                TextField::class,
                TextFieldOption::make()
                    ->label(__('username'))
                    ->value(BaseHelper::hasDemoModeEnabled() ? '*******************************' : get_payment_setting('username', BKASHPAY_PAYMENT_METHOD_NAME))
                    ->toArray()
            )
            ->add(
                sprintf('payment_%s_password', BKASHPAY_PAYMENT_METHOD_NAME),
                'text',
                TextFieldOption::make()
                    ->label(__('Password'))
                    ->value(BaseHelper::hasDemoModeEnabled() ? '*******************************' : get_payment_setting('password', BKASHPAY_PAYMENT_METHOD_NAME))
                    ->toArray()
            )
            ->add(
                sprintf('payment_%s_appKey', BKASHPAY_PAYMENT_METHOD_NAME),
                'text',
                TextFieldOption::make()
                    ->label(__('app_key'))
                    ->attributes(['data-counter' => 400])
                    ->value(BaseHelper::hasDemoModeEnabled() ? '*******************************' : get_payment_setting('appKey', BKASHPAY_PAYMENT_METHOD_NAME))
                    ->toArray()
            )

            ->add(
                sprintf('payment_%s_appSecretKey', BKASHPAY_PAYMENT_METHOD_NAME),
                'text',
                TextFieldOption::make()
                    ->label(__('app_secret_key'))
                    ->attributes(['data-counter' => 400])
                    ->value(BaseHelper::hasDemoModeEnabled() ? '*******************************' : get_payment_setting('appSecretKey', BKASHPAY_PAYMENT_METHOD_NAME))
                    ->toArray()
            )
            ->add(
                sprintf('payment_%s_mode', BKASHPAY_PAYMENT_METHOD_NAME),
                OnOffCheckboxField::class,
                CheckboxFieldOption::make()
                    ->label(trans('plugins/payment::payment.live_mode'))
                    ->value(get_payment_setting('mode', BKASHPAY_PAYMENT_METHOD_NAME, false))
                    ->toArray(),
            );
    }
}
