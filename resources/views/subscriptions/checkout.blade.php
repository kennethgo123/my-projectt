<x-app-layout>
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">Subscribe to {{ $plan->name }}</h2>
                    <a href="{{ route('subscriptions.index') }}" class="text-indigo-600 hover:text-indigo-800">
                        &larr; Back to plans
                    </a>
                </div>
                
                <div class="bg-gray-50 rounded-lg p-4 mb-6">
                    <div class="flex justify-between">
                        <div>
                            <h3 class="text-lg font-semibold">{{ $plan->name }} Plan</h3>
                            <p class="text-gray-600">{{ $plan->description }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xl font-bold">₱{{ number_format($plan->monthly_price, 2) }}<span class="text-sm text-gray-500">/month</span></p>
                            <p class="text-gray-600">₱{{ number_format($plan->annual_price, 2) }}/year</p>
                        </div>
                    </div>
                </div>
                
                <div id="subscription-form">
                    <form id="payment-form" class="space-y-6">
                        @csrf
                        <input type="hidden" name="plan_id" id="plan_id" value="{{ $plan->id }}">
                        
                        <div>
                            <label class="block font-medium text-gray-700 mb-2">Billing Cycle</label>
                            <div class="flex space-x-4">
                                <label class="flex items-center">
                                    <input type="radio" name="billing_cycle" value="monthly" class="h-4 w-4 text-indigo-600" checked>
                                    <span class="ml-2">Monthly (₱{{ number_format($plan->monthly_price, 2) }})</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="billing_cycle" value="annual" class="h-4 w-4 text-indigo-600">
                                    <span class="ml-2">Annual (₱{{ number_format($plan->annual_price, 2) }}) <span class="text-green-600 text-sm">Save {{ round((1 - $plan->annual_price / ($plan->monthly_price * 12)) * 100) }}%</span></span>
                                </label>
                            </div>
                        </div>
                        
                        <div>
                            <label for="payment_method" class="block font-medium text-gray-700 mb-2">Payment Method</label>
                            <div class="relative">
                                <select id="payment_method" name="payment_method" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block w-full p-2.5">
                                    <option value="card">Credit/Debit Card</option>
                                    <option value="gcash">GCash</option>
                                </select>
                            </div>
                        </div>
                        
                        <div id="card-element" class="bg-white p-3 border border-gray-300 rounded-md mb-4">
                            <!-- Card details form for credit card payment -->
                            <div id="card-details-form" class="space-y-4">
                                <div>
                                    <label for="card_name" class="block text-sm font-medium text-gray-700">Name on Card</label>
                                    <input type="text" id="card_name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" value="{{ Auth::user()->first_name }} {{ Auth::user()->last_name }}">
                                </div>
                                <div>
                                    <label for="card_number" class="block text-sm font-medium text-gray-700">Card Number</label>
                                    <div class="relative rounded-md shadow-sm">
                                        <input type="text" id="card_number" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm pr-16" placeholder="4123 4567 8910 1112">
                                        <!-- Card Logos -->
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none space-x-1">
                                            <svg class="h-5 w-auto text-blue-600" viewBox="0 0 38 24" xmlns="http://www.w3.org/2000/svg" role="img" aria-labelledby="pi-visa"><title id="pi-visa">Visa</title><path opacity=".07" d="M35 0H3C1.3 0 0 1.3 0 3v18c0 1.7 1.4 3 3 3h32c1.7 0 3-1.3 3-3V3c0-1.7-1.4-3-3-3z"/><path fill="#fff" d="M34.5 20V4H3.5v16h31z"/><path fill="#1A1F71" d="M14.9 4.1h-2.9c-.9 0-1.8.6-2.1 1.5L7.2 18c-.3.8.1 1.7.9 2.1.3.1.6.2.9.2h2.9c.9 0 1.8-.6 2.1-1.5L16.8 6c.3-.8-.1-1.7-.9-2.1-.3-.1-.6-.2-.9-.2zm16.6 0h-2.9c-.9 0-1.8.6-2.1 1.5L23.8 18c-.3.8.1 1.7.9 2.1.3.1.6.2.9.2h2.9c.9 0 1.8-.6 2.1-1.5l2.7-12.5c.3-.8-.1-1.7-.9-2.1-.3-.1-.6-.2-.9-.2z"/></svg>
                                            <svg class="h-5 w-auto" viewBox="0 0 38 24" xmlns="http://www.w3.org/2000/svg" role="img" aria-labelledby="pi-mastercard"><title id="pi-mastercard">Mastercard</title><path opacity=".07" d="M35 0H3C1.3 0 0 1.3 0 3v18c0 1.7 1.4 3 3 3h32c1.7 0 3-1.3 3-3V3c0-1.7-1.4-3-3-3z"/><path fill="#fff" d="M34.5 20V4H3.5v16h31z"/><circle fill="#EB001B" cx="15" cy="12" r="7"/><circle fill="#F79E1B" cx="23" cy="12" r="7"/><path fill="#FF5F00" d="M22 12c0-3.9-3.1-7-7-7s-7 3.1-7 7 3.1 7 7 7 7-3.1 7-7z"/></svg>
                                        </div>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="card_expiry" class="block text-sm font-medium text-gray-700">Expiration Date</label>
                                        <input type="text" id="card_expiry" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="MM/YY">
                                    </div>
                                    <div>
                                        <label for="card_cvc" class="block text-sm font-medium text-gray-700">CVC</label>
                                        <input type="text" id="card_cvc" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="123">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div id="card-errors" role="alert" class="text-red-600 text-sm min-h-[1.25rem]"></div>
                        
                        <div class="mt-6">
                            <h4 class="font-semibold mb-2">Summary</h4>
                            <div class="bg-gray-50 p-4 rounded-md">
                                <div class="flex justify-between py-2">
                                    <span>{{ $plan->name }} Plan (<span id="billing-text">Monthly</span>)</span>
                                    <span id="price-display">₱{{ number_format($plan->monthly_price, 2) }}</span>
                                </div>
                                <div class="flex justify-between py-2 border-t border-gray-200">
                                    <span class="font-semibold">Total</span>
                                    <span class="font-semibold" id="total-price">₱{{ number_format($plan->monthly_price, 2) }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex items-center">
                            <input type="checkbox" id="terms" name="terms" checked class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            <label for="terms" class="ml-2 block text-sm text-gray-900">
                                I agree to the <a href="{{ route('terms') }}" target="_blank" class="text-indigo-600 hover:text-indigo-800">Terms and Conditions</a>
                            </label>
                        </div>
                        
                        <div class="flex justify-end pt-4">
                            <button type="submit" id="submit-button" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded focus:outline-none focus:shadow-outline">
                                Pay Now
                            </button>
                        </div>
                    </form>
                </div>
                
                <div id="payment-processing" class="hidden text-center py-12">
                    <svg class="animate-spin h-10 w-10 text-indigo-600 mx-auto mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="text-lg font-medium text-gray-800">Processing your payment...</p>
                    <p class="text-gray-600">Please do not close this window.</p>
                </div>
            </div>
        </div>
    </div>
    
    @push('scripts')
    <script src="https://js.paymongo.com/v2/paymongo.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Set up the billing cycle change event
            const billingCycleInputs = document.querySelectorAll('input[name="billing_cycle"]');
            const priceDisplay = document.getElementById('price-display');
            const totalPrice = document.getElementById('total-price');
            const billingText = document.getElementById('billing-text');
            
            const monthlyPrice = {{ $plan->monthly_price }};
            const annualPrice = {{ $plan->annual_price }};
            
            billingCycleInputs.forEach(input => {
                input.addEventListener('change', function() {
                    if (this.value === 'monthly') {
                        priceDisplay.textContent = '₱' + monthlyPrice.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                        totalPrice.textContent = '₱' + monthlyPrice.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                        billingText.textContent = 'Monthly';
                    } else {
                        priceDisplay.textContent = '₱' + annualPrice.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                        totalPrice.textContent = '₱' + annualPrice.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                        billingText.textContent = 'Annual';
                    }
                });
            });
            
            // Toggle payment method display
            const paymentMethodSelect = document.getElementById('payment_method');
            const cardElement = document.getElementById('card-element');
            
            paymentMethodSelect.addEventListener('change', function() {
                if (this.value === 'card') {
                    cardElement.classList.remove('hidden');
                } else {
                    cardElement.classList.add('hidden');
                }
            });
            
            // Handle form submission
            const form = document.getElementById('payment-form');
            const submitButton = document.getElementById('submit-button');
            const paymentProcessing = document.getElementById('payment-processing');
            const subscriptionForm = document.getElementById('subscription-form');
            const errorElement = document.getElementById('card-errors');
            
            // Add formatting for credit card input
            const cardNumberInput = document.getElementById('card_number');
            const cardExpiryInput = document.getElementById('card_expiry');
            const cardCvcInput = document.getElementById('card_cvc');
            
            // Format card number with spaces
            cardNumberInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 16) value = value.substr(0, 16);
                
                // Format with spaces every 4 digits
                e.target.value = value.replace(/(\d{4})(?=\d)/g, '$1 ').trim();
            });
            
            // Format expiry date as MM/YY
            cardExpiryInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 4) value = value.substr(0, 4);
                
                if (value.length > 2) {
                    e.target.value = value.substr(0, 2) + '/' + value.substr(2);
                } else {
                    e.target.value = value;
                }
            });
            
            // Limit CVC to 3-4 digits
            cardCvcInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 4) value = value.substr(0, 4);
                e.target.value = value;
            });
            
            // Valid card number check using Luhn algorithm
            function isValidCardNumber(cardNumber) {
                const digitsOnly = cardNumber.replace(/\s+/g, '');
                
                if (!/^\d{13,19}$/.test(digitsOnly)) {
                    return false;
                }
                
                // Skip Luhn validation for test environment
                if ('{{ config('services.paymongo.public_key') }}'.startsWith('pk_test_')) {
                    return true;
                }
                
                // Luhn algorithm
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
            }
            
            form.addEventListener('submit', async function(event) {
                event.preventDefault();
                
                // Disable submit button
                submitButton.disabled = true;
                submitButton.textContent = 'Processing...';
                errorElement.textContent = '';
                
                const paymentMethodType = paymentMethodSelect.value;
                
                // For GCash payment, redirect to server immediately
                if (paymentMethodType === 'gcash') {
                    // Show processing indicator
                    subscriptionForm.classList.add('hidden');
                    paymentProcessing.classList.remove('hidden');
                    
                    try {
                        // Create payment source on the server
                        const response = await fetch("{{ route('subscriptions.create-payment-intent') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                            },
                            body: JSON.stringify({
                                plan_id: document.getElementById('plan_id').value,
                                billing_cycle: document.querySelector('input[name="billing_cycle"]:checked').value,
                                payment_method: 'gcash'
                            })
                        });
                        
                        const data = await response.json();
                        
                        if (data.error) {
                            throw new Error(data.error);
                        }
                        
                        if (data.checkout_url) {
                            // Redirect to GCash checkout page
                            window.location.href = data.checkout_url;
                        } else {
                            throw new Error('No checkout URL provided');
                        }
                    } catch (error) {
                        console.error(error);
                        subscriptionForm.classList.remove('hidden');
                        paymentProcessing.classList.add('hidden');
                        submitButton.disabled = false;
                        submitButton.textContent = 'Pay Now';
                        errorElement.textContent = error.message || 'An error occurred. Please try again.';
                    }
                    
                    return;
                }
                
                // For card payment
                const cardName = document.getElementById('card_name').value;
                const cardNumber = document.getElementById('card_number').value.replace(/\s+/g, '');
                const cardExpiry = document.getElementById('card_expiry').value;
                const cardCvc = document.getElementById('card_cvc').value;
                
                // Basic validation first
                if (!cardNumber || !cardExpiry || !cardCvc || !cardName) {
                    errorElement.textContent = 'Please complete all card fields';
                    submitButton.disabled = false;
                    submitButton.textContent = 'Pay Now';
                    return;
                }
                
                // Validate card number
                if (!isValidCardNumber(cardNumber)) {
                    errorElement.textContent = 'Please enter a valid card number';
                    submitButton.disabled = false;
                    submitButton.textContent = 'Pay Now';
                    return;
                }
                
                // Parse expiry date
                const expiryParts = cardExpiry.split('/');
                if (expiryParts.length !== 2) {
                    errorElement.textContent = 'Please enter a valid expiry date (MM/YY)';
                    submitButton.disabled = false;
                    submitButton.textContent = 'Pay Now';
                    return;
                }
                
                const expMonth = parseInt(expiryParts[0], 10);
                const expYear = parseInt(expiryParts[1], 10);
                
                if (expMonth < 1 || expMonth > 12) {
                    errorElement.textContent = 'Invalid expiry month (must be 1-12)';
                    submitButton.disabled = false;
                    submitButton.textContent = 'Pay Now';
                    return;
                }
                
                // Validate CVC
                if (!/^\d{3,4}$/.test(cardCvc)) {
                    errorElement.textContent = 'Invalid CVC';
                    submitButton.disabled = false;
                    submitButton.textContent = 'Pay Now';
                    return;
                }
                
                // Show processing indicator
                subscriptionForm.classList.add('hidden');
                paymentProcessing.classList.remove('hidden');
                
                try {
                    // First, create a payment intent on the server
                    const intentResponse = await fetch("{{ route('subscriptions.create-payment-intent') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                        },
                        body: JSON.stringify({
                            plan_id: document.getElementById('plan_id').value,
                            billing_cycle: document.querySelector('input[name="billing_cycle"]:checked').value,
                            payment_method: 'card'
                        })
                    });
                    
                    const intentData = await intentResponse.json();
                    
                    if (intentData.error) {
                        throw new Error(intentData.error);
                    }
                    
                    const paymentIntentId = intentData.payment_intent_id;
                    
                    // Now create a payment method directly with PayMongo API
                    const paymentMethodData = {
                        data: {
                            attributes: {
                                type: 'card',
                                details: {
                                    card_number: cardNumber,
                                    exp_month: parseInt(expMonth, 10),
                                    exp_year: parseInt('20' + expYear, 10),
                                    cvc: cardCvc
                                },
                                billing: {
                                    name: cardName,
                                    email: '{{ Auth::user()->email }}'
                                }
                            }
                        }
                    };
                    
                    const pmResponse = await fetch('https://api.paymongo.com/v1/payment_methods', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Authorization': 'Basic ' + btoa('{{ config('services.paymongo.public_key') }}' + ':')
                        },
                        body: JSON.stringify(paymentMethodData)
                    });
                    
                    if (!pmResponse.ok) {
                        const pmErrorText = await pmResponse.text();
                        let errorMessage = 'Payment method creation failed';
                        
                        try {
                            const pmErrorJson = JSON.parse(pmErrorText);
                            if (pmErrorJson.errors && pmErrorJson.errors[0] && pmErrorJson.errors[0].detail) {
                                errorMessage = pmErrorJson.errors[0].detail;
                            }
                        } catch (e) {
                            // Use default error message
                        }
                        
                        throw new Error(errorMessage);
                    }
                    
                    const paymentMethodResult = await pmResponse.json();
                    const paymentMethodId = paymentMethodResult.data.id;
                    
                    // Submit to our server to attach payment method and process subscription
                    const processResponse = await fetch("{{ route('subscriptions.process-card-payment') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                        },
                        body: JSON.stringify({
                            payment_method_id: paymentMethodId,
                            payment_intent_id: paymentIntentId
                        })
                    });
                    
                    const processResult = await processResponse.json();
                    
                    if (!processResponse.ok || !processResult.success) {
                        throw new Error(processResult.message || 'Payment processing failed');
                    }
                    
                    // Check if we need to handle 3D Secure
                    if (processResult.requires_action && processResult.redirect_url) {
                        window.location.href = processResult.redirect_url;
                        return;
                    }
                    
                    // Direct redirect to account subscription page
                    window.location.href = "{{ route('account.subscription') }}";
                    
                } catch (error) {
                    console.error(error);
                    subscriptionForm.classList.remove('hidden');
                    paymentProcessing.classList.add('hidden');
                    submitButton.disabled = false;
                    submitButton.textContent = 'Pay Now';
                    errorElement.textContent = error.message || 'An error occurred during payment. Please try again.';
                }
            });
        });
    </script>
    @endpush
</x-app-layout> 