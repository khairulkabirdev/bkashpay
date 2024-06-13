<?php

namespace Khairulkabir\BkashPay\Http\Controllers;

use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Payment\Supports\PaymentHelper;
use Illuminate\Routing\Controller;
use Khairulkabir\BkashPay\Http\Requests\BkashPayPaymentCallbackRequest;
use Khairulkabir\BkashPay\Services\Gateways\BkashPayPaymentService;

class BkashPayController extends Controller
{
    public function getCallback(BkashPayPaymentCallbackRequest $request, BkashPayPaymentService $BkashPayPaymentService, BaseHttpResponse $response)
    {
        $status = $BkashPayPaymentService->getPaymentStatus($request);

        if (! isset($status['statusCode']) || $status['statusCode'] !== '0000') {

            return $response
                ->setError()
                ->setNextUrl(PaymentHelper::getCancelURL())
                ->withInput()
                ->setMessage(__('Payment failed!'));
        }

        $BkashPayPaymentService->afterMakePayment($request->input(), $status);

        $token = $BkashPayPaymentService->getToken($request->input());

        return $response
            ->setNextUrl(PaymentHelper::getRedirectURL($token))
            ->setMessage(__('Checkout successfully!'));
    }
}
