<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Str;

class PaymentService
{
    /**
     * Process a mock credit card payment.
     * Simulates success/failure based on card number pattern.
     * Card numbers ending in '0000' will fail; all others succeed.
     */
    public function processCreditCard(Order $order, array $cardData): Payment
    {
        $cardLastFour = substr($cardData['card_number'], -4);
        $shouldFail = $cardLastFour === '0000';

        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'credit_card',
            'amount' => $order->total,
            'status' => $shouldFail ? 'failed' : 'success',
            'transaction_id' => $shouldFail ? null : 'TXN-CC-' . strtoupper(Str::random(12)),
            'card_last_four' => $cardLastFour,
            'failure_reason' => $shouldFail ? 'Card declined. Please try a different card.' : null,
        ]);

        if ($payment->isSuccessful()) {
            $order->update(['status' => 'confirmed']);
        }

        ActivityLogService::log('payment_attempt', 'Payment', $payment->id, [
            'order_id' => $order->id,
            'method' => 'credit_card',
            'status' => $payment->status,
            'amount' => $payment->amount,
            'card_last_four' => $cardLastFour,
            'failure_reason' => $payment->failure_reason,
        ]);

        return $payment;
    }

    /**
     * Process a mock PayPal payment.
     * Simulates success/failure based on email pattern.
     * Emails containing 'fail' will fail; all others succeed.
     */
    public function processPayPal(Order $order, array $paypalData): Payment
    {
        $paypalEmail = $paypalData['paypal_email'];
        $shouldFail = str_contains(strtolower($paypalEmail), 'fail');

        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'paypal',
            'amount' => $order->total,
            'status' => $shouldFail ? 'failed' : 'success',
            'transaction_id' => $shouldFail ? null : 'TXN-PP-' . strtoupper(Str::random(12)),
            'paypal_email' => $paypalEmail,
            'failure_reason' => $shouldFail ? 'PayPal payment declined. Please try again.' : null,
        ]);

        if ($payment->isSuccessful()) {
            $order->update(['status' => 'confirmed']);
        }

        ActivityLogService::log('payment_attempt', 'Payment', $payment->id, [
            'order_id' => $order->id,
            'method' => 'paypal',
            'status' => $payment->status,
            'amount' => $payment->amount,
            'paypal_email' => $paypalEmail,
            'failure_reason' => $payment->failure_reason,
        ]);

        return $payment;
    }
}
