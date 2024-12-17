<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class MpesaService
{
    protected $base_url;
    protected $consumer_key;
    protected $consumer_secret;
    protected $shortcode;
    protected $passkey;
    protected $initiator_name;
    protected $initiator_password;
    protected $callback_url;

    public function __construct()
    {
        $this->base_url = config('mpesa.env') === 'production'
            ? 'https://api.safaricom.co.ke'
            : 'https://sandbox.safaricom.co.ke';
        $this->consumer_key = config('mpesa.consumer_key');
        $this->consumer_secret = config('mpesa.consumer_secret');
        $this->shortcode = config('mpesa.shortcode');
        $this->passkey = config('mpesa.passkey');
        $this->initiator_name = config('mpesa.initiator_name');
        $this->initiator_password = config('mpesa.initiator_password');
        $this->callback_url = config('mpesa.callback_url');
    }

    public function getAccessToken()
    {
        $url = $this->base_url . '/oauth/v1/generate?grant_type=client_credentials';
        $response = Http::withBasicAuth($this->consumer_key, $this->consumer_secret)
            ->get($url);

        return $response->json()['access_token'];
    }

    public function stkPush($amount, $phone, $accountReference, $transactionDesc)
    {
        $url = $this->base_url . '/mpesa/stkpush/v1/processrequest';
        $timestamp = now()->format('YmdHis');
        $password = base64_encode($this->shortcode . $this->passkey . $timestamp);
        $accessToken = $this->getAccessToken();

        $response = Http::withToken($accessToken)->post($url, [
            "BusinessShortCode" => $this->shortcode,
            "Password" => $password,
            "Timestamp" => $timestamp,
            "TransactionType" => "CustomerPayBillOnline",
            "Amount" => $amount,
            "PartyA" => $phone,
            "PartyB" => $this->shortcode,
            "PhoneNumber" => $phone,
            "CallBackURL" => route('mpesa.stk_callback'),
            "AccountReference" => $accountReference,
            "TransactionDesc" => $transactionDesc
        ]);

        return $response->json();
    }

    public function registerC2bUrl()
    {
        $url = $this->base_url . '/mpesa/c2b/v1/registerurl';
        $accessToken = $this->getAccessToken();

        $response = Http::withToken($accessToken)->post($url, [
            "ShortCode" => $this->shortcode,
            "ResponseType" => "Completed",
            "ConfirmationURL" => route('mpesa.c2b_confirmation'),
            "ValidationURL" => route('mpesa.c2b_validation'),
        ]);

        return $response->json();
    }

    public function b2cPayment($amount, $phone, $remarks)
    {
        $url = $this->base_url . '/mpesa/b2c/v1/paymentrequest';
        $accessToken = $this->getAccessToken();

        $securityCredential = $this->getSecurityCredential();

        $response = Http::withToken($accessToken)->post($url, [
            "InitiatorName" => $this->initiator_name,
            "SecurityCredential" => $securityCredential,
            "CommandID" => "BusinessPayment",
            "Amount" => $amount,
            "PartyA" => $this->shortcode,
            "PartyB" => $phone,
            "Remarks" => $remarks,
            "QueueTimeOutURL" => route('mpesa.b2c_timeout'),
            "ResultURL" => route('mpesa.b2c_result'),
            "Occasion" => ""
        ]);

        return $response->json();
    }

    public function getSecurityCredential()
    {
        $publicKey = storage_path('app/public/mpesa_public_cert.cer');

        openssl_public_encrypt($this->initiator_password, $encrypted, file_get_contents($publicKey), OPENSSL_PKCS1_PADDING);

        return base64_encode($encrypted);
    }

    public function transactionStatus($transactionId)
    {
        $url = $this->base_url . '/mpesa/transactionstatus/v1/query';
        $accessToken = $this->getAccessToken();
        $securityCredential = $this->getSecurityCredential();

        $response = Http::withToken($accessToken)->post($url, [
            "Initiator" => $this->initiator_name,
            "SecurityCredential" => $securityCredential,
            "CommandID" => "TransactionStatusQuery",
            "TransactionID" => $transactionId,
            "PartyA" => $this->shortcode,
            "IdentifierType" => 4, // 4 for shortcode
            "ResultURL" => route('mpesa.transaction_status_result'),
            "QueueTimeOutURL" => route('mpesa.transaction_status_timeout'),
            "Remarks" => "Transaction Status Query",
            "Occasion" => ""
        ]);

        return $response->json();
    }
}
