<?php
namespace App\Services;
use Illuminate\Support\Facades\Http;

use Srmklive\PayPal\Services\PayPal as PayPalClient;

class PayPalService
{
    protected $paypal;

    public function __construct()
    {
        $this->paypal = new PayPalClient();
        $this->paypal->setApiCredentials(config('paypal'));
        $this->paypal->getAccessToken();
    }

    public function createPayment($amount)
    {
        // return response()->json([
        //     'amount'=>$amount
        // ],200);
       
        $provider = new PayPalClient;

        $provider->setApiCredentials(config('paypal'));

        $paypalToken = $provider->getAccessToken();

  

        $response = $provider->createOrder([

            "intent" => "CAPTURE",


            "purchase_units" => [

                0 => [

                    "amount" => [

                        "currency_code" => "USD",

                        "value" => $amount

                    ]

                ]

            ]

        ]);

   

        return $response;
    }

    public function capturePayment($orderId)
    {
        return $this->paypal->capturePaymentOrder($orderId);
    }
}
