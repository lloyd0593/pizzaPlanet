@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-extrabold text-gray-900 mb-6">
        <i class="fas fa-credit-card text-orange-500 mr-2"></i> Payment
    </h1>

    {{-- Order Total --}}
    <div class="bg-orange-50 border border-orange-200 rounded-xl p-4 mb-6 flex items-center justify-between">
        <span class="text-gray-700 font-semibold">Order #{{ $order->id }} Total:</span>
        <span class="text-2xl font-extrabold text-orange-600">€{{ number_format($order->total, 2) }}</span>
    </div>

    @if($errors->any())
        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
            <ul class="list-disc list-inside text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-lg p-6">
        <form method="POST" action="{{ route('checkout.processPayment', $order->id) }}" id="paymentForm">
            @csrf

            {{-- Payment Method Tabs --}}
            <div class="flex gap-4 mb-6">
                <button type="button" onclick="selectPaymentMethod('credit_card')" id="tab-credit_card"
                        class="flex-1 py-3 px-4 rounded-lg border-2 border-orange-500 bg-orange-50 text-orange-700 font-bold transition text-center">
                    <i class="fas fa-credit-card mr-2"></i> Credit Card
                </button>
                <button type="button" onclick="selectPaymentMethod('paypal')" id="tab-paypal"
                        class="flex-1 py-3 px-4 rounded-lg border-2 border-gray-200 bg-white text-gray-600 font-bold transition text-center hover:border-blue-400">
                    <i class="fab fa-paypal mr-2"></i> PayPal
                </button>
            </div>

            <input type="hidden" name="payment_method" id="payment_method" value="credit_card">

            {{-- Credit Card Form --}}
            <div id="form-credit_card">
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Cardholder Name</label>
                    <input type="text" name="card_name" value="{{ old('card_name', 'John Doe') }}"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                           placeholder="Name on card">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Card Number</label>
                    <input type="text" name="card_number" value="{{ old('card_number') }}" maxlength="16"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 font-mono"
                           placeholder="4242424242424242">
                    <p class="text-xs text-gray-400 mt-1">
                        <i class="fas fa-info-circle mr-1"></i> Use any 16-digit number. Ending in 0000 will simulate failure.
                    </p>
                </div>
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Expiry (MM/YY)</label>
                        <input type="text" name="card_expiry" value="{{ old('card_expiry', '12/28') }}" maxlength="5"
                               class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 font-mono"
                               placeholder="12/28">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">CVV</label>
                        <input type="text" name="card_cvv" value="{{ old('card_cvv') }}" maxlength="3"
                               class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 font-mono"
                               placeholder="123">
                    </div>
                </div>
            </div>

            {{-- PayPal Form --}}
            <div id="form-paypal" class="hidden">
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">PayPal Email</label>
                    <input type="email" name="paypal_email" value="{{ old('paypal_email') }}"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="your@email.com">
                    <p class="text-xs text-gray-400 mt-1">
                        <i class="fas fa-info-circle mr-1"></i> Emails containing "fail" will simulate payment failure.
                    </p>
                </div>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                    <div class="flex items-center space-x-3">
                        <i class="fab fa-paypal text-blue-600 text-3xl"></i>
                        <div>
                            <p class="font-semibold text-blue-800">Mock PayPal Payment</p>
                            <p class="text-sm text-blue-600">You will be redirected to a simulated PayPal checkout.</p>
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit"
                    class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg transition shadow-lg hover:shadow-xl text-lg mt-4">
                <i class="fas fa-lock mr-2"></i> Pay €{{ number_format($order->total, 2) }}
            </button>
        </form>
    </div>

    <div class="mt-4 text-center text-sm text-gray-400">
        <i class="fas fa-shield-alt mr-1"></i> This is a mock payment system. No real charges will be made.
    </div>
</div>

<script>
    function selectPaymentMethod(method) {
        document.getElementById('payment_method').value = method;

        // Toggle forms
        document.getElementById('form-credit_card').classList.toggle('hidden', method !== 'credit_card');
        document.getElementById('form-paypal').classList.toggle('hidden', method !== 'paypal');

        // Toggle tab styles
        const ccTab = document.getElementById('tab-credit_card');
        const ppTab = document.getElementById('tab-paypal');

        if (method === 'credit_card') {
            ccTab.className = 'flex-1 py-3 px-4 rounded-lg border-2 border-orange-500 bg-orange-50 text-orange-700 font-bold transition text-center';
            ppTab.className = 'flex-1 py-3 px-4 rounded-lg border-2 border-gray-200 bg-white text-gray-600 font-bold transition text-center hover:border-blue-400';
        } else {
            ppTab.className = 'flex-1 py-3 px-4 rounded-lg border-2 border-blue-500 bg-blue-50 text-blue-700 font-bold transition text-center';
            ccTab.className = 'flex-1 py-3 px-4 rounded-lg border-2 border-gray-200 bg-white text-gray-600 font-bold transition text-center hover:border-orange-400';
        }
    }
</script>
@endsection
