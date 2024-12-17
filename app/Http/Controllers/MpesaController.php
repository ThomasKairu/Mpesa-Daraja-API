<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\MpesaService;

class MpesaController extends Controller
{
    public function stkPushCallback(Request $request)
    {
        Log::info('STK Push Callback:', $request->all());

        // Process the callback data as needed.

        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }

    public function c2bConfirmation(Request $request)
    {
        Log::info('C2B Confirmation:', $request->all());
        // Process the confirmation data

        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }

    public function c2bValidation(Request $request)
    {
        Log::info('C2B Validation:', $request->all());
        // Validate the transaction

        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }

    public function b2cResult(Request $request)
    {
        Log::info('B2C Result:', $request->all());
        // Process the B2C result

        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }

    public function b2cTimeout(Request $request)
    {
        Log::info('B2C Timeout:', $request->all());
        // Handle the timeout

        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }

    public function transactionStatusResult(Request $request)
    {
        Log::info('Transaction Status Result:', $request->all());
        // Process the result

        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }

    public function transactionStatusTimeout(Request $request)
    {
        Log::info('Transaction Status Timeout:', $request->all());
        // Handle timeout

        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }

    public function stkPush(Request $request, MpesaService $mpesaService)
    {
        $amount = $request->input('amount');
        $phone = $request->input('phone');
        $accountReference = 'Ref001';
        $transactionDesc = 'Payment for Order 001';

        $response = $mpesaService->stkPush($amount, $phone, $accountReference, $transactionDesc);

        return response()->json($response);
    }

    public function b2cPayment(Request $request, MpesaService $mpesaService)
    {
        $amount = $request->input('amount');
        $phone = $request->input('phone');
        $remarks = $request->input('remarks');

        $response = $mpesaService->b2cPayment($amount, $phone, $remarks);

        return response()->json($response);
    }

    public function transactionStatus(Request $request, MpesaService $mpesaService)
    {
        $transactionId = $request->input('transaction_id');

        $response = $mpesaService->transactionStatus($transactionId);

        return response()->json($response);
    }
}
