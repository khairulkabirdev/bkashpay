<?php

namespace Khairulkabir\BkashPay\Services\Gateways;

use Botble\Ecommerce\Enums\OrderStatusEnum;
use Botble\Ecommerce\Models\Order;
use Botble\Payment\Enums\PaymentStatusEnum;
use Botble\Payment\Repositories\Interfaces\PaymentInterface;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class BkashPayPaymentService
{


    protected string $username;
    protected string $password;
    protected string $appKey;
    protected string $appSecrectKey;

    public string $bkashURL;

    public function __construct()
    {
        $this->username = setting('payment_bkashpay_username');
        $this->password = setting('payment_bkashpay_password');
        $this->appKey = setting('payment_bkashpay_appKey');
        $this->appSecrectKey = setting('payment_bkashpay_appSecretKey');
        $this->bkashURL = setting('payment_bkashpay_mode') == 1 ? 'https://tokenized.pay.bka.sh/v1.2.0-beta/' : 'https://tokenized.sandbox.bka.sh/v1.2.0-beta/';
    }


    public function makePayment(array $data)
{
    $tokenBK = $this->initToken();
   
    // Generate the return URL
    $BK_callback = route('payments.bkashpay.callback', ['auth' => $tokenBK, 'order_id' =>$data['orders'][0]->id]);
    
    $Amount = $data['amount']; // Payment amount
   
    $requestData = [
        'mode' => '0011',
        'amount' => $Amount,
        'currency' => 'BDT',
        'intent' => 'sale',
        'payerReference' => '0', // Replace with the actual payer reference
        'callbackURL' => $BK_callback,
        'merchantInvoiceNumber' => 'invoice_' . Str::random(15),
    ];

    $response = $this
        ->getRequestBK($tokenBK)
        ->post($this->bkashURL.'tokenized/checkout/create', $requestData);
        
    $responseData = $response->json();

   

    if (isset($responseData['bkashURL'])) {
        return $responseData['bkashURL'];
    }

}


    public function afterMakePayment($data, $response): string
    {
        $chargeId = $response['trxID'];
        $order = Order::find($data['order_id']);
        if($order !== NULL) {
            $customer = $order->user;
            do_action(PAYMENT_ACTION_PAYMENT_PROCESSED, [
                'amount' => $response['amount'],
                'currency' => 'BDT',
                'charge_id' => $chargeId,
                'order_id' => $order->id,
                'customer_id' => $customer->id,
                'customer_type' => get_class($customer),
                'payment_channel' => BKASHPAY_PAYMENT_METHOD_NAME,
                'status' =>PaymentStatusEnum::COMPLETED,
            ]);
        }

        return $chargeId;

       
    }


    public function getPaymentStatus($request)
    {

        $paymentID = $request->paymentID;
        $tokent = $request->auth;

        $requestData = array('paymentID'=>$paymentID);

        $response = $this->getRequestBK($tokent)
        ->post($this->bkashURL.'tokenized/checkout/execute', $requestData);

        $responseData = $response->json();

        return $responseData;

     

    }

    public function getToken($data)
    {
        $order = Order::find($data['order_id']);
        return $order->token;
    }

    public function supportedCurrencyCodes(): array
    {
        return ['BDT'];
    }


    protected function getTokenRequest(): PendingRequest
    {
        $request = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'username' => $this->username,
            'password' =>  $this->password,
        ]);

        $request->withoutVerifying();

        return $request;
    }

    public function getRequestBK($auth): PendingRequest
    {
        $request = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => $auth,
            'X-APP-Key' =>$this->appKey,
        ]);
         $request->withoutVerifying();
        return $request;
    }


    public function initToken(): string
    {
        $requestData = [
            'app_key' => $this->appKey,
            'app_secret' => $this->appSecrectKey,
        ];



        $response = $this
            ->getTokenRequest()
            ->post($this->bkashURL.'tokenized/checkout/token/grant', $requestData);
            if ($response->successful()) {
                $responseData = $response->json(); // Parse JSON response data
                // Check if statusCode is "0000" indicating success
                if (isset($responseData['statusCode']) && $responseData['statusCode'] === "0000") {
                    if (isset($responseData['id_token'])) {

                 

                        return $responseData['id_token']; // Return id_token if successful
                    } else {
                        return '';
                    }
                } else {
                    return '';
                }
            } else {
                return '';
            }

    }


























}
