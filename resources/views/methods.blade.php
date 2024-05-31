@if (setting('payment_bkashpay_status') == 1)
    <li class="list-group-item">
        <input class="magic-radio js_payment_method" type="radio" name="payment_method" id="payment_bkashpay"
               @if ($selecting == BKASHPAY_PAYMENT_METHOD_NAME
) checked @endif
               value="bkashpay" data-bs-toggle="collapse" data-bs-target=".payment_bkashpay_wrap" data-toggle="collapse" data-target=".payment_bkashpay_wrap" data-parent=".list_payment_method">
        <label for="payment_bkashpay" class="text-start">{{ setting('payment_bkashpay_name', trans('plugins/bkashpay::bkashpay.payment_via_bkashpay')) }}</label>
        <div class="payment_bkashpay_wrap payment_collapse_wrap collapse @if ($selecting == BKASHPAY_PAYMENT_METHOD_NAME
) show @endif" style="padding: 15px 0;">
            <p>{!! BaseHelper::clean(setting('payment_bkashpay_description')) !!}</p>
        </div>
    </li>
@endif
