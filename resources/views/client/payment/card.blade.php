@extends('layouts.app')

@section('title', 'Card Payment')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-lg mx-auto bg-white rounded-lg shadow-xl overflow-hidden border border-gray-200">
        <div class="py-4 px-6 bg-gradient-to-r from-blue-600 to-blue-700">
            <h2 class="text-xl font-semibold text-white">Payment Details</h2>
        </div>
        
        <div class="p-6">
            <div class="mb-6 pb-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-800">Invoice #{{ $invoice->invoice_number }}</h3>
                <p class="text-sm text-gray-500">{{ $invoice->title }}</p>
                
                @if($invoice->payment_plan !== \App\Models\Invoice::PAYMENT_PLAN_FULL)
                    <div class="mt-2">
                        <p class="text-gray-900 font-bold text-lg">
                            Installment Payment: PHP {{ number_format($invoice->getInstallmentAmount(), 2) }}
                        </p>
                        <p class="text-sm text-gray-600">
                            @if($invoice->payment_plan === \App\Models\Invoice::PAYMENT_PLAN_3_MONTHS)
                                Payment {{ $invoice->installments_paid + 1 }} of 3 (3-Month Plan)
                            @elseif($invoice->payment_plan === \App\Models\Invoice::PAYMENT_PLAN_6_MONTHS)
                                Payment {{ $invoice->installments_paid + 1 }} of 6 (6-Month Plan)
                            @elseif($invoice->payment_plan === \App\Models\Invoice::PAYMENT_PLAN_1_YEAR)
                                Payment {{ $invoice->installments_paid + 1 }} of 12 (1-Year Plan)
                            @endif
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            Total Invoice: PHP {{ number_format($invoice->total, 2) }}
                        </p>
                    </div>
                @else
                    <p class="text-gray-900 font-bold text-lg mt-2">Total Amount: PHP {{ number_format($invoice->total, 2) }}</p>
                @endif
            </div>
            
            <form id="payment-form" class="space-y-6">
                {{-- Name on Card --}}
                <div>
                    <label for="card-name" class="block text-sm font-medium text-gray-700 mb-1">Name on Card</label>
                    <input 
                        id="card-name" 
                        type="text" 
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm h-11 px-4 py-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        value="{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}" 
                        required
                    >
                </div>
            
                {{-- Card Number with Logos --}}
                <div>
                    <label for="card-number" class="block text-sm font-medium text-gray-700 mb-1">Card Number</label>
                    <div class="relative rounded-md shadow-sm">
                        <input 
                            id="card-number" 
                            type="text" 
                            class="mt-1 block w-full border border-gray-300 rounded-md h-11 px-4 py-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm pr-16" {{-- Added padding for logos --}}
                            placeholder="0000 0000 0000 0000" 
                            required
                        >
                        {{-- Card Logos --}}
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none space-x-1">
                            {{-- Visa SVG Logo (Simplified) --}}
                            <svg class="h-5 w-auto text-blue-600" viewBox="0 0 38 24" xmlns="http://www.w3.org/2000/svg" role="img" aria-labelledby="pi-visa"><title id="pi-visa">Visa</title><path opacity=".07" d="M35 0H3C1.3 0 0 1.3 0 3v18c0 1.7 1.4 3 3 3h32c1.7 0 3-1.3 3-3V3c0-1.7-1.4-3-3-3z"/><path fill="#fff" d="M34.5 20V4H3.5v16h31z"/><path fill="#1A1F71" d="M14.9 4.1h-2.9c-.9 0-1.8.6-2.1 1.5L7.2 18c-.3.8.1 1.7.9 2.1.3.1.6.2.9.2h2.9c.9 0 1.8-.6 2.1-1.5L16.8 6c.3-.8-.1-1.7-.9-2.1-.3-.1-.6-.2-.9-.2zm16.6 0h-2.9c-.9 0-1.8.6-2.1 1.5L23.8 18c-.3.8.1 1.7.9 2.1.3.1.6.2.9.2h2.9c.9 0 1.8-.6 2.1-1.5l2.7-12.5c.3-.8-.1-1.7-.9-2.1-.3-.1-.6-.2-.9-.2z"/></svg>
                            {{-- Mastercard SVG Logo (Simplified) --}}
                            <svg class="h-5 w-auto" viewBox="0 0 38 24" xmlns="http://www.w3.org/2000/svg" role="img" aria-labelledby="pi-mastercard"><title id="pi-mastercard">Mastercard</title><path opacity=".07" d="M35 0H3C1.3 0 0 1.3 0 3v18c0 1.7 1.4 3 3 3h32c1.7 0 3-1.3 3-3V3c0-1.7-1.4-3-3-3z"/><path fill="#fff" d="M34.5 20V4H3.5v16h31z"/><circle fill="#EB001B" cx="15" cy="12" r="7"/><circle fill="#F79E1B" cx="23" cy="12" r="7"/><path fill="#FF5F00" d="M22 12c0-3.9-3.1-7-7-7s-7 3.1-7 7 3.1 7 7 7 7-3.1 7-7z"/></svg>
                        </div>
                    </div>
                </div>
                
                {{-- Expiry Date and CVC --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="card-expiry" class="block text-sm font-medium text-gray-700 mb-1">Expiration Date</label>
                        <input 
                            id="card-expiry" 
                            type="text" 
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm h-11 px-4 py-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm" 
                            placeholder="MM/YY" 
                            required
                        >
                    </div>
                    <div>
                        <label for="card-cvc" class="block text-sm font-medium text-gray-700 mb-1">CVC</label>
                        <input 
                            id="card-cvc" 
                            type="text" 
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm h-11 px-4 py-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm" 
                            placeholder="123" 
                            required
                        >
                    </div>
                </div>
                
                {{-- Payment Errors --}}
                <div id="payment-errors" class="text-red-600 text-sm min-h-[1.25rem]"></div> {{-- Added min-height --}}
                
                {{-- Pay Button --}}
                <button id="pay-button" type="submit" class="w-full px-4 py-3 bg-blue-600 text-white font-semibold rounded-md shadow-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-200 ease-in-out">
                    @if($invoice->payment_plan !== \App\Models\Invoice::PAYMENT_PLAN_FULL)
                        Pay Installment PHP {{ number_format($invoice->getInstallmentAmount(), 2) }}
                    @else
                        Pay PHP {{ number_format($invoice->total, 2) }}
                    @endif
                </button>
            </form>
            
            {{-- PayMongo Footer --}}
             <div class="mt-6 text-center text-xs text-gray-400">
                <span class="inline-block align-middle">Secure payments powered by</span>
                <img src="https://assets.paymongo.com/images/paymongo-logo-dark.svg" alt="PayMongo" class="h-4 inline-block align-middle ml-1">
            </div>
        </div>
    </div>
</div>

<!-- Payment Success Modal -->
<div 
    x-data="{ showSuccessModal: false, redirectUrl: '', invoicesUrl: '' }" 
    x-init="
        $watch('showSuccessModal', value => { 
            if(value) document.body.style.overflow = 'hidden'; 
            else document.body.style.overflow = 'auto'; 
        });
        window.addEventListener('show-payment-success', (event) => {
            redirectUrl = event.detail.redirectUrl;
            invoicesUrl = event.detail.invoicesUrl || '{{ route("client.invoices") }}';
            console.log('Modal URLs set:', { redirectUrl, invoicesUrl });
            showSuccessModal = true;
        });
    "
    x-show="showSuccessModal" 
    x-cloak
    class="fixed inset-0 flex items-center justify-center z-50" 
    style="display: none;"
>
    <!-- Backdrop -->
    <div x-show="showSuccessModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black bg-opacity-50"></div>
    
    <!-- Modal Content -->
    <div 
        x-show="showSuccessModal" 
        x-transition:enter="transition ease-out duration-300" 
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
        x-transition:leave="transition ease-in duration-200" 
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
        class="bg-white rounded-lg shadow-xl p-6 mx-4 sm:mx-auto sm:max-w-lg relative z-10 transform transition-all"
    >
        <div class="text-center">
            <!-- Success Icon -->
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
                <svg class="h-10 w-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            
            <!-- Title -->
            <h3 class="text-2xl font-bold text-gray-900 mb-2">Payment Successful!</h3>
            
            <!-- Content -->
            <div class="mb-6">
                <p class="text-gray-700 mb-4">Your payment has been successfully processed.</p>
                
                <div class="bg-gray-50 rounded-lg p-4 mb-4 text-left">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-gray-700">Invoice:</span>
                        <span class="font-semibold">#{{ $invoice->invoice_number }}</span>
                    </div>
                    
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-gray-700">Amount:</span>
                        <span class="font-semibold">
                            @if($invoice->payment_plan !== \App\Models\Invoice::PAYMENT_PLAN_FULL)
                                PHP {{ number_format($invoice->getInstallmentAmount(), 2) }}
                            @else
                                PHP {{ number_format($invoice->total, 2) }}
                            @endif
                        </span>
                    </div>
                    
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-gray-700">Payment Method:</span>
                        <span class="font-semibold">Credit/Debit Card</span>
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <span class="text-gray-700">Date:</span>
                        <span class="font-semibold">{{ now()->format('M d, Y h:i A') }}</span>
                    </div>
                </div>
                
                @if($invoice->payment_plan !== \App\Models\Invoice::PAYMENT_PLAN_FULL)
                    <div class="text-left bg-blue-50 rounded-lg p-4 border border-blue-200">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-blue-800">
                                    This is payment {{ $invoice->installments_paid + 1 }} of 
                                    @if($invoice->payment_plan === \App\Models\Invoice::PAYMENT_PLAN_3_MONTHS)
                                        3
                                    @elseif($invoice->payment_plan === \App\Models\Invoice::PAYMENT_PLAN_6_MONTHS)
                                        6
                                    @elseif($invoice->payment_plan === \App\Models\Invoice::PAYMENT_PLAN_1_YEAR)
                                        12
                                    @endif
                                    for your installment plan.
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            
            <!-- Action Buttons -->
            <div class="flex justify-center space-x-3">
                <button 
                    type="button"
                    @click="console.log('View Invoices clicked, going to:', invoicesUrl); window.location.href = invoicesUrl"
                    class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-white text-sm tracking-wide hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                >
                    View Invoices
                </button>
                <button 
                    type="button"
                    @click="console.log('Proceed clicked, going to:', redirectUrl); window.location.href = redirectUrl"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white text-sm tracking-wide hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                    Proceed with Booking Your Consultation
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://js.paymongo.com/v2/paymongo.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM Content Loaded. Starting PayMongo script...');

        // Check if PayMongo constructor is available
        if (typeof PayMongo === 'undefined') {
            console.error('PayMongo class is not available. The PayMongo JS SDK might not have loaded correctly.');
            document.getElementById('payment-errors').textContent = 'Payment SDK failed to load. Please refresh or contact support.';
            return;
        }
        console.log('PayMongo class is available');

        const paymongoPublicKey = "{{ $publicKey }}";
        console.log('Using PayMongo public key:', paymongoPublicKey);
        
        const paymongo = new PayMongo(paymongoPublicKey);
        console.log('PayMongo SDK instance created');
        
        const form = document.getElementById('payment-form');
        const payButton = document.getElementById('pay-button');
        const errorElement = document.getElementById('payment-errors');
        
        // Add test mode flag - if using a test API key, we'll allow test cards
        const isTestMode = paymongoPublicKey.startsWith('pk_test_');
        console.log('Running in', isTestMode ? 'TEST mode' : 'LIVE mode');
        
        // PayMongo test cards that should be allowed
        const validTestCards = [
            '4343434343434345', // Visa test card (success)
            '4571736000000075', // Visa debit test card
            '5555444444444457', // Updated Mastercard test card format
            '4009930000001421', // Visa credit - PH
            '4404520000001439', // Visa debit - PH
            '5240050000001440'  // Mastercard credit - PH
        ];
        
        const displayError = (error) => {
            console.error('Payment Error:', error);
            
            // More detailed error logging
            if (error.details) {
                console.error('Error details:', error.details);
            }
            
            if (error.errors && Array.isArray(error.errors)) {
                console.error('API Errors:', error.errors);
                
                if (error.errors[0] && error.errors[0].detail) {
                    errorElement.textContent = error.errors[0].detail;
                    console.error('Error detail:', error.errors[0].detail);
                    payButton.disabled = false;
                    
                    @if($invoice->payment_plan !== \App\Models\Invoice::PAYMENT_PLAN_FULL)
                        payButton.textContent = 'Pay Installment PHP {{ number_format($invoice->getInstallmentAmount(), 2) }}';
                    @else
                        payButton.textContent = 'Pay PHP {{ number_format($invoice->total, 2) }}';
                    @endif
                    
                    return;
                }
            }
            
            let errorMessage = 'An error occurred while processing your payment.';
            
            // Extract more specific error message if available
            if (error.message) {
                errorMessage = error.message;
            } else if (error.details && error.details.message) {
                errorMessage = error.details.message;
            } else if (typeof error === 'string') {
                errorMessage = error;
            }
            
            console.error('Displaying error message:', errorMessage);
            errorElement.textContent = errorMessage;
            payButton.disabled = false;
            
            @if($invoice->payment_plan !== \App\Models\Invoice::PAYMENT_PLAN_FULL)
                payButton.textContent = 'Pay Installment PHP {{ number_format($invoice->getInstallmentAmount(), 2) }}';
            @else
                payButton.textContent = 'Pay PHP {{ number_format($invoice->total, 2) }}';
            @endif
        };
        
        // Add improved validation and formatting
        const isValidCardNumber = (cardNumber) => {
            // Remove spaces and check if it's a valid length (13-19 digits)
            const digitsOnly = cardNumber.replace(/\s+/g, '');
            
            if (!/^\d{13,19}$/.test(digitsOnly)) {
                return false;
            }
            
            // In test mode, accept PayMongo test cards regardless of Luhn check
            if (isTestMode && validTestCards.includes(digitsOnly)) {
                return true;
            }
            
            // Skip Luhn validation for test mode with cards starting with '43434'
            if (isTestMode && digitsOnly.startsWith('43434')) {
                return true;
            }
            
            // Luhn algorithm validation for real cards
            let sum = 0;
            let shouldDouble = false;
            
            // Loop from right to left
            for (let i = digitsOnly.length - 1; i >= 0; i--) {
                let digit = parseInt(digitsOnly.charAt(i));
                
                if (shouldDouble) {
                    digit *= 2;
                    if (digit > 9) {
                        digit -= 9;
                    }
                }
                
                sum += digit;
                shouldDouble = !shouldDouble;
            }
            
            return sum % 10 === 0;
        };
        
        // Format according to card type
        const formatCardNumber = (cardNumber) => {
            // Remove all non-digit characters
            const digitsOnly = cardNumber.replace(/\D/g, '');
            
            // Different card types have different formats
            let formatted = digitsOnly;
            
            // Visa, Mastercard, etc. (default 4-4-4-4 grouping)
            if (digitsOnly.length <= 16) {
                formatted = digitsOnly.replace(/(\d{4})(?=\d)/g, '$1 ');
            }
            
            return formatted.trim();
        };
        
        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            console.log('Payment form submitted');
            
            payButton.disabled = true;
            errorElement.textContent = '';
            payButton.textContent = 'Processing...';
            
            // Get form values
            const cardName = document.getElementById('card-name').value;
            const cardNumber = document.getElementById('card-number').value.replace(/\s+/g, '');
            const cardExpiry = document.getElementById('card-expiry').value;
            const cardCvc = document.getElementById('card-cvc').value;
            
            // Basic validation first
            if (!cardNumber || !cardExpiry || !cardCvc || !cardName) {
                displayError({ message: 'Please complete all card fields' });
                return;
            }
            
            // Validate card number
            if (!isValidCardNumber(cardNumber)) {
                displayError({ message: 'Please enter a valid card number' });
                return;
            }
            
            // Parse expiry date
            const [expiryMonth, expiryYear] = cardExpiry.split('/');
            
            if (!expiryMonth || !expiryYear || expiryYear.length !== 2) {
                displayError({ message: 'Please enter a valid expiry date (MM/YY)' });
                return;
            }
            
            // Validate expiry date
            const currentYear = new Date().getFullYear() % 100; // Get last 2 digits
            const currentMonth = new Date().getMonth() + 1; // 1-12
            
            const expMonth = parseInt(expiryMonth, 10);
            const expYear = parseInt(expiryYear, 10);
            
            if (expMonth < 1 || expMonth > 12) {
                displayError({ message: 'Invalid expiry month (must be 1-12)' });
                return;
            }
            
            if (expYear < currentYear || (expYear === currentYear && expMonth < currentMonth)) {
                displayError({ message: 'Card has expired' });
                return;
            }
            
             // Validate CVC
            if (!/^\d{3,4}$/.test(cardCvc)) {
                displayError({ message: 'Invalid CVC' });
                return;
            }
            
            try {
                console.log('Creating payment method...');
                
                // Sanitize card number - ensure it's digits only
                const sanitizedCardNumber = cardNumber.replace(/\D/g, '');
                
                // Use a known working card number format for our problematic test card
                let finalCardNumber = sanitizedCardNumber;
                
                // Special handling for test card - replace with known working format if needed
                if (isTestMode && sanitizedCardNumber === '4343434343434345') {
                    console.log('Using alternate format for problematic test card');
                    // Use one of PayMongo's other test cards which may work better
                    finalCardNumber = '4343434343434345';
                }
                
                // Format according to PayMongo API docs
                const paymentMethodData = {
                    data: {
                        attributes: {
                            type: 'card',
                            details: {
                                card_number: finalCardNumber,
                                exp_month: parseInt(expMonth, 10),
                                exp_year: parseInt(2000 + expYear, 10),
                                cvc: cardCvc
                            },
                            billing: {
                                name: cardName,
                                email: '{{ auth()->user()->email }}'
                            }
                        }
                    }
                };
                
                // Debug the exact card number format (showing only last 4 digits for security)
                const cardLast4 = finalCardNumber.slice(-4);
                const cardLength = finalCardNumber.length;
                console.log(`Card number format check - Length: ${cardLength}, Last 4: ${cardLast4}, Contains spaces: ${cardNumber.includes(' ')}`);
                
                console.log('Payment method data structure:', JSON.stringify(paymentMethodData, (key, value) => {
                    if (key === 'card_number') return 'XXXX-XXXX-XXXX-' + value.slice(-4);
                    if (key === 'cvc') return 'XXX';
                    return value;
                }));
                
                // Using manual fetch instead of SDK for better control
                const pmResponse = await fetch('https://api.paymongo.com/v1/payment_methods', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Basic ' + btoa(paymongoPublicKey + ':')
                    },
                    body: JSON.stringify(paymentMethodData)
                });
                
                console.log('Payment method creation status:', pmResponse.status);
                
                if (!pmResponse.ok) {
                    const pmErrorText = await pmResponse.text();
                    console.error('Payment method creation error response:', pmErrorText);
                    
                    // Try to log the exact request that failed
                    console.log('Failed request details:', {
                        url: 'https://api.paymongo.com/v1/payment_methods',
                        method: 'POST',
                        card_number_length: finalCardNumber.length,
                        card_number_first6: finalCardNumber.slice(0, 6),
                        card_number_last4: finalCardNumber.slice(-4),
                        exp_month: parseInt(expMonth, 10),
                        exp_year: parseInt(2000 + expYear, 10)
                    });
                    
                    try {
                        const pmErrorJson = JSON.parse(pmErrorText);
                        
                        // Enhanced error handling for card validation errors
                        if (pmErrorJson.errors && Array.isArray(pmErrorJson.errors)) {
                            const cardErrors = pmErrorJson.errors.filter(err => 
                                err.code === 'parameter_format_invalid' || 
                                err.code === 'parameter_invalid' ||
                                (err.detail && err.detail.toLowerCase().includes('card'))
                            );
                            
                            if (cardErrors.length > 0) {
                                // Map PayMongo error codes to user-friendly messages
                                const errorMap = {
                                    'parameter_format_invalid': 'The card information .',
                                    'parameter_invalid': 'The card information is invalid.'
                                };
                                
                                // Get the first card error
                                const cardError = cardErrors[0];
                                let errorMessage = errorMap[cardError.code] || cardError.detail;
                                
                                // Check for specific field errors
                                if (cardError.code === 'parameter_format_invalid' && 
                                    cardError.detail && 
                                    cardError.detail.includes('card_number')) {
                                    errorMessage = 'The card number format is invalid. Please check and try again.';
                                    
                                    // If in test mode, provide additional guidance
                                    if (isTestMode) {
                                        errorMessage += ' For testing, try using one of these valid test cards: 4571736000000075 or 5555444444444457';
                                    }
                                }
                                
                                console.error('Card validation error:', cardError);
                                throw { message: errorMessage, errors: pmErrorJson.errors };
                            }
                        }
                        
                        throw pmErrorJson;
                    } catch (e) {
                        if (e.errors && Array.isArray(e.errors)) {
                            throw e;
                        }
                        throw { message: 'Failed to create payment method' };
                    }
                }
                
                const paymentMethodResult = await pmResponse.json();
                console.log('Payment method creation result:', paymentMethodResult);
                
                if (!paymentMethodResult.data || !paymentMethodResult.data.id) {
                    console.error('Payment method missing ID:', paymentMethodResult);
                    throw new Error('Invalid payment method response from PayMongo');
                }
                
                const paymentMethodId = paymentMethodResult.data.id;
                console.log('Payment method created successfully with ID:', paymentMethodId);
                
                // Submit to our server
                console.log('Submitting payment method ID to server...');
                
                const requestData = {
                    payment_method_id: paymentMethodId,
                    payment_intent_id: '{{ $paymentIntentId }}',
                    invoice_id: {{ $invoice->id }}
                };
                
                console.log('Request data:', requestData);
                
                const response = await fetch('{{ route("client.payment.card.process") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(requestData)
                });
                
                console.log('Server response status:', response.status);
                
                if (!response.ok) {
                    console.error('Server response not OK. Status:', response.status);
                    const errorText = await response.text();
                    console.error('Error response text:', errorText);
                    
                    try {
                        // Try to parse as JSON if possible
                        const errorJson = JSON.parse(errorText);
                        throw { message: errorJson.message || 'Payment processing failed on server', details: errorJson };
                    } catch (e) {
                        // If parsing fails, use the text directly
                        throw { message: 'Payment processing failed on server', details: { text: errorText } };
                    }
                }
                
                const result = await response.json();
                console.log('Server response JSON:', result);
                
                if (result.success) {
                    console.log('Payment successful, result:', result);
                    console.log('redirectUrl (Book Consultation):', result.redirect);
                    console.log('invoicesUrl:', result.invoicesUrl);
                    if (result.showSuccessModal) {
                        // Show success modal using Alpine.js
                        window.dispatchEvent(new CustomEvent('show-payment-success', {
                            detail: {
                                redirectUrl: result.redirect,
                                invoicesUrl: result.invoicesUrl
                            }
                        }));
                    } else {
                        // Fallback to direct redirect if no modal flag
                        window.location.href = result.redirect;
                    }
                } else {
                    console.error('Server reported payment error:', result);
                    displayError({ message: result.message || 'Payment processing failed on the server' });
                }
                
            } catch (error) {
                console.error('Error during payment submission:', error);
                displayError(error);
            }
        });
        
        // Add formatting for credit card input with improved validation
        const cardNumberInput = document.getElementById('card-number');
        cardNumberInput.addEventListener('input', (e) => {
            // Store cursor position
            const cursorPosition = e.target.selectionStart;
            const oldValue = e.target.value;
            const oldLength = oldValue.length;
            
            // Get only digits
            let value = e.target.value.replace(/\D/g, ''); // Remove non-digit characters
            
            // Limit to maximum 19 digits (standard for most cards)
            if (value.length > 19) {
                value = value.substring(0, 19);
            }
            
            // Format the value
            const formattedValue = formatCardNumber(value);
            e.target.value = formattedValue;
            
            // Adjust cursor position based on added/removed spaces
            const newLength = formattedValue.length;
            let newPosition = cursorPosition + (newLength - oldLength);
            
            // Don't let the cursor position go negative
            newPosition = Math.max(0, Math.min(newPosition, newLength));
            
            // Set cursor position
            e.target.setSelectionRange(newPosition, newPosition);
        });
        
        // Add better formatting for expiry date
        const expiryInput = document.getElementById('card-expiry');
        expiryInput.addEventListener('input', (e) => {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 0) {
                // Auto-format month
                if (value.length >= 1) {
                    // If first digit is > 1, prepend 0
                    if (parseInt(value[0], 10) > 1) {
                        value = '0' + value;
                    }
                    // If first two digits > 12, set to 12
                    if (value.length >= 2 && parseInt(value.substring(0, 2), 10) > 12) {
                        value = '12' + value.substring(2);
                    }
                }
                
                // Add slash after month
                if (value.length > 2) {
                    value = value.substring(0, 2) + '/' + value.substring(2);
                }
                
                // Limit to MM/YY format (5 chars)
                value = value.substring(0, 5);
            }
            e.target.value = value;
        });
        
        // Add formatting for CVC (limit to 3-4 digits)
        const cvcInput = document.getElementById('card-cvc');
        cvcInput.addEventListener('input', (e) => {
            let value = e.target.value.replace(/\D/g, '');
            e.target.value = value.substring(0, 4);
        });
    });
</script>
@endpush 