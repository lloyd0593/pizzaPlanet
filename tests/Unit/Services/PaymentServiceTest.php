<?php

namespace Tests\Unit\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    private PaymentService $paymentService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->paymentService = new PaymentService();
    }

    private function createPendingOrder(float $total = 25.00): Order
    {
        return Order::create([
            'session_id' => session()->getId(),
            'customer_name' => 'Test Customer',
            'customer_email' => 'test@example.com',
            'subtotal' => round($total / 1.08, 2),
            'tax' => round($total - ($total / 1.08), 2),
            'total' => $total,
            'status' => 'pending',
        ]);
    }

    // ─── Credit Card Payments ───────────────────────────────────────

    public function test_credit_card_payment_success(): void
    {
        $order = $this->createPendingOrder(30.00);

        $payment = $this->paymentService->processCreditCard($order, [
            'card_number' => '4111111111111111',
            'card_expiry' => '12/28',
            'card_cvv' => '123',
        ]);

        $this->assertInstanceOf(Payment::class, $payment);
        $this->assertEquals('success', $payment->status);
        $this->assertEquals('credit_card', $payment->payment_method);
        $this->assertEquals(30.00, (float) $payment->amount);
        $this->assertEquals('1111', $payment->card_last_four);
        $this->assertNotNull($payment->transaction_id);
        $this->assertStringStartsWith('TXN-CC-', $payment->transaction_id);
        $this->assertNull($payment->failure_reason);

        // Order should be confirmed
        $order->refresh();
        $this->assertEquals('confirmed', $order->status);
    }

    public function test_credit_card_payment_failure_ending_0000(): void
    {
        $order = $this->createPendingOrder(25.00);

        $payment = $this->paymentService->processCreditCard($order, [
            'card_number' => '4111111111110000',
            'card_expiry' => '12/28',
            'card_cvv' => '123',
        ]);

        $this->assertEquals('failed', $payment->status);
        $this->assertEquals('0000', $payment->card_last_four);
        $this->assertNull($payment->transaction_id);
        $this->assertNotNull($payment->failure_reason);
        $this->assertStringContainsString('declined', $payment->failure_reason);

        // Order should remain pending
        $order->refresh();
        $this->assertEquals('pending', $order->status);
    }

    public function test_credit_card_stores_last_four_digits(): void
    {
        $order = $this->createPendingOrder();

        $payment = $this->paymentService->processCreditCard($order, [
            'card_number' => '5500000000005678',
        ]);

        $this->assertEquals('5678', $payment->card_last_four);
    }

    public function test_credit_card_payment_creates_activity_log(): void
    {
        $order = $this->createPendingOrder();

        $this->paymentService->processCreditCard($order, [
            'card_number' => '4111111111111111',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'payment_attempt',
            'entity_type' => 'Payment',
        ]);
    }

    // ─── PayPal Payments ────────────────────────────────────────────

    public function test_paypal_payment_success(): void
    {
        $order = $this->createPendingOrder(40.00);

        $payment = $this->paymentService->processPayPal($order, [
            'paypal_email' => 'customer@paypal.com',
        ]);

        $this->assertInstanceOf(Payment::class, $payment);
        $this->assertEquals('success', $payment->status);
        $this->assertEquals('paypal', $payment->payment_method);
        $this->assertEquals(40.00, (float) $payment->amount);
        $this->assertEquals('customer@paypal.com', $payment->paypal_email);
        $this->assertNotNull($payment->transaction_id);
        $this->assertStringStartsWith('TXN-PP-', $payment->transaction_id);
        $this->assertNull($payment->failure_reason);

        // Order should be confirmed
        $order->refresh();
        $this->assertEquals('confirmed', $order->status);
    }

    public function test_paypal_payment_failure_with_fail_in_email(): void
    {
        $order = $this->createPendingOrder(20.00);

        $payment = $this->paymentService->processPayPal($order, [
            'paypal_email' => 'fail_user@paypal.com',
        ]);

        $this->assertEquals('failed', $payment->status);
        $this->assertNull($payment->transaction_id);
        $this->assertNotNull($payment->failure_reason);
        $this->assertStringContainsString('declined', $payment->failure_reason);

        // Order should remain pending
        $order->refresh();
        $this->assertEquals('pending', $order->status);
    }

    public function test_paypal_failure_case_insensitive(): void
    {
        $order = $this->createPendingOrder();

        $payment = $this->paymentService->processPayPal($order, [
            'paypal_email' => 'FAIL@example.com',
        ]);

        $this->assertEquals('failed', $payment->status);
    }

    public function test_paypal_payment_creates_activity_log(): void
    {
        $order = $this->createPendingOrder();

        $this->paymentService->processPayPal($order, [
            'paypal_email' => 'test@paypal.com',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'payment_attempt',
            'entity_type' => 'Payment',
        ]);
    }

    // ─── Payment Model ──────────────────────────────────────────────

    public function test_is_successful_returns_true_for_success(): void
    {
        $order = $this->createPendingOrder();
        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'credit_card',
            'amount' => 25.00,
            'status' => 'success',
            'transaction_id' => 'TXN-TEST-123',
        ]);

        $this->assertTrue($payment->isSuccessful());
    }

    public function test_is_successful_returns_false_for_failed(): void
    {
        $order = $this->createPendingOrder();
        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'credit_card',
            'amount' => 25.00,
            'status' => 'failed',
            'failure_reason' => 'Declined',
        ]);

        $this->assertFalse($payment->isSuccessful());
    }
}
