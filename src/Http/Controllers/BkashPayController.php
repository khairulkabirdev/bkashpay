<?php

namespace Khairulkabir\BkashPay\Http\Controllers;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Khairulkabir\BkashPay\Http\Requests\BkashPayPaymentCallbackRequest;
use Khairulkabir\BkashPay\Services\Gateways\BkashPayPaymentService;
use Botble\Payment\Supports\PaymentHelper;
use Illuminate\Routing\Controller;

class BkashPayController extends Controller
{
    /**
     * Get callback from BkashPay
     *
     * @param BkashPayPaymentCallbackRequest $request
     * @param BkashPayPaymentService $BkashPayPaymentService
     * @param BaseHttpResponse $response
     * @return void
     */
    public function getCallback( BkashPayPaymentCallbackRequest $request, BkashPayPaymentService $BkashPayPaymentService, BaseHttpResponse $response) {
        $status = $BkashPayPaymentService->getPaymentStatus($request);
        $token = null;


        if (!isset($status['statusCode']) || $status['statusCode'] !== '0000') {
         
            return $response
                ->setError()
                ->setNextUrl(PaymentHelper::getCancelURL())
                ->withInput()
                ->setMessage(__('Payment failed!sssssss'));
        }
      
            $BkashPayPaymentService->afterMakePayment($request->input(), $status );

            $token = $BkashPayPaymentService->getToken($request->input());


    
        return $response
            ->setNextUrl(PaymentHelper::getRedirectURL($token))
            ->setMessage(__('Checkout successfully!'));
    }
}
