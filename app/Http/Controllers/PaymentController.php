<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Services\PayMongoService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Payment;

class PaymentController extends Controller
{
    /**
     * Handle PayMongo webhook events
     */
    public function webhook(Request $request)
    {
        Log::info('PayMongo webhook received', ['payload' => $request->all()]);
        
        // Validate webhook signature if provided in headers
        // This helps ensure that the webhook request is from PayMongo
        $payMongoSignature = $request->header('PayMongo-Signature');
        if ($payMongoSignature) {
            $webhookSecret = config('services.paymongo.webhook_secret');
            $payload = $request->getContent();
            $computedSignature = hash_hmac('sha256', $payload, $webhookSecret);
            
            if (!hash_equals($computedSignature, $payMongoSignature)) {
                Log::warning('Invalid PayMongo webhook signature');
                return response()->json(['error' => 'Invalid signature'], 400);
            }
        }
        
        // Process the webhook
        $payMongoService = new PayMongoService();
        $result = $payMongoService->processWebhook($request->all());
        
        return response()->json(['status' => $result['success'] ? 'success' : 'error', 'message' => $result['message'] ?? '']);
    }
    
    /**
     * Handle successful payment redirect
     */
    public function success(Request $request, $invoiceId)
    {
        $invoice = Invoice::findOrFail($invoiceId);
        
        // If we don't have webhooks, we need to check the source/payment status manually
        if ($invoice->paymongo_source_id) {
            // Check source status
            $payMongoService = new PayMongoService();
            $sourceStatus = $payMongoService->checkSourceStatus($invoice->paymongo_source_id);
            
            if ($sourceStatus['success'] && $sourceStatus['status'] === 'chargeable') {
                // Create a payment manually since we don't have webhooks
                $paymentResult = $payMongoService->createPaymentFromSource($invoice, $invoice->paymongo_source_id);
                
                if ($paymentResult['success']) {
                    // Payment was successful
                    $next = $request->query('next');
                    if ($next) {
                        return redirect($next)->with('success', 'Your payment was successful. Thank you!');
                    }
                    if ($invoice->legal_case_id) {
                        return redirect()->route('client.case.view', [
                            'case' => $invoice->legal_case_id,
                            'tab' => 'invoices'
                        ])->with('success', 'Your payment was successful. Thank you!');
                    } else {
                        return redirect()->route('client.invoices')->with('success', 'Your payment was successful. Thank you!');
                    }
                }
            }
        }
        
        // Default message for when we can't confirm the payment status
        $next = $request->query('next');
        if ($next) {
            return redirect($next)->with('success', 'Your payment is being processed. You will receive confirmation soon.');
        }
        if ($invoice->legal_case_id) {
            return redirect()->route('client.case.view', [
                'case' => $invoice->legal_case_id,
                'tab' => 'invoices'
            ])->with('success', 'Your payment is being processed. You will receive confirmation soon.');
        } else {
            return redirect()->route('client.invoices')->with('success', 'Your payment is being processed. You will receive confirmation soon.');
        }
    }
    
    /**
     * Handle failed payment redirect
     */
    public function failed(Request $request, $invoiceId)
    {
        $invoice = Invoice::findOrFail($invoiceId);
        
        // Redirect to case view with error message
        if ($invoice->legal_case_id) {
            return redirect()->route('client.case.view', [
                'case' => $invoice->legal_case_id,
                'tab' => 'invoices'
            ])->with('error', 'Your payment could not be processed. Please try again or contact support.');
        } else {
            return redirect()->route('client.invoices')->with('error', 'Your payment could not be processed. Please try again or contact support.');
        }
    }

    /**
     * Show card payment page
     */
    public function showCardPayment(Request $request, $invoiceId)
    {
        $invoice = Invoice::findOrFail($invoiceId);
        
        // Check if this is the first time visiting this page
        if (!session('payment_intent_id') || !session('client_key')) {
            // Create a new payment intent directly
            $payMongoService = new PayMongoService();
            $result = $payMongoService->createPaymentIntent($invoice);
            
            if (!$result['success']) {
                if ($invoice->legal_case_id) {
                    return redirect()->route('client.case.view', [
                        'case' => $invoice->legal_case_id,
                        'tab' => 'invoices'
                    ])->with('error', 'Failed to initialize payment: ' . ($result['message'] ?? 'Unknown error'));
                } else {
                    return redirect()->route('client.invoices')->with('error', 'Failed to initialize payment: ' . ($result['message'] ?? 'Unknown error'));
                }
            }
            
            // Store session data
            session([
                'payment_intent_id' => $result['intent_id'],
                'client_key' => $result['client_key'],
                'invoice_id' => $invoice->id
            ]);
        }
        
        return view('client.payment.card', [
            'invoice' => $invoice,
            'paymentIntentId' => session('payment_intent_id'),
            'clientKey' => session('client_key'),
            'publicKey' => config('services.paymongo.public_key'),
            'redirectUrl' => $request->query('redirect') // pass to view
        ]);
    }
    
    /**
     * Process card payment
     */
    public function processCardPayment(Request $request)
    {
        // Get data from request or fall back to session
        $invoiceId = $request->input('invoice_id') ?? session('invoice_id');
        $paymentIntentId = $request->input('payment_intent_id') ?? session('payment_intent_id');
        $paymentMethodId = $request->input('payment_method_id');
        
        Log::info('Processing card payment', [
            'invoice_id' => $invoiceId,
            'payment_intent_id' => $paymentIntentId,
        ]);
        
        if (!$invoiceId || !$paymentIntentId) {
            return response()->json([
                'success' => false,
                'message' => 'Payment session expired. Please try again.'
            ]);
        }
        
        if (!$paymentMethodId) {
            return response()->json([
                'success' => false,
                'message' => 'No payment method provided.'
            ]);
        }
        
        try {
            $invoice = Invoice::findOrFail($invoiceId);
            
            // Attach payment method to the payment intent
            $payMongoService = new PayMongoService();
            $result = $payMongoService->attachPaymentMethod($paymentIntentId, $paymentMethodId);
            
            if (!$result['success']) {
                Log::error('Failed to attach payment method', [
                    'invoice_id' => $invoiceId,
                    'error' => $result['message'] ?? 'Unknown error'
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Payment failed. Please try again.'
                ]);
            }
            
            // Check payment status
            $status = $result['status'] ?? '';
            if ($status === 'succeeded' || $status === 'processing') {
                // Create a payment record
                $paymentId = $paymentIntentId;
                
                // Determine the amount paid (could be an installment)
                $amountPaid = $invoice->payment_plan !== Invoice::PAYMENT_PLAN_FULL 
                    ? $invoice->getInstallmentAmount() 
                    : $invoice->total;
                
                // Ensure the amount recorded doesn't exceed invoice total if it's the final payment or full payment
                if ($invoice->payment_plan === Invoice::PAYMENT_PLAN_FULL || 
                    ($invoice->payment_plan !== Invoice::PAYMENT_PLAN_FULL && ($invoice->installments_paid + 1) >= $invoice->getTotalInstallments())) {
                    $paidAmountSoFar = $invoice->payments()->where('status', Payment::STATUS_SUCCESS)->sum('amount');
                    if ($paidAmountSoFar + $amountPaid > $invoice->total) {
                        $amountPaid = $invoice->total - $paidAmountSoFar; 
                    }
                }
                // Ensure amount paid is not negative
                $amountPaid = max(0, $amountPaid);

                $payment = Payment::create([
                    'invoice_id' => $invoice->id,
                    'client_id' => $invoice->client_id,
                    'amount' => $amountPaid,
                    'payment_method' => 'card',
                    'transaction_id' => $paymentId,
                    'status' => $status === 'succeeded' ? Payment::STATUS_SUCCESS : Payment::STATUS_PENDING,
                    'payment_date' => now(),
                    'paymongo_payment_id' => $paymentId
                ]);
                
                // Update invoice status (this will also handle installments_paid)
                $invoice->updateStatus();
                
                // Clear session data
                session()->forget(['payment_intent_id', 'client_key', 'invoice_id']);
                
                // Redirect to appropriate page based on whether there's a legal case
                $redirectUrl = $invoice->legal_case_id 
                    ? route('client.case.view', ['case' => $invoice->legal_case_id, 'tab' => 'invoices'])
                    : route('client.invoices');
                
                // Send notification to the lawyer
                NotificationService::paymentReceived($invoice, $payment);
                
                // After successful payment
                // Mark invoice as paid if not already
                if ($invoice->status !== \App\Models\Invoice::STATUS_PAID) {
                    $invoice->updateStatus();
                }
                // If this is a consultation reservation, set session for BookConsultation
                if ($invoice->title === 'Consultation Reservation') {
                    session(['reservation_paid_for_booking' => true, 'reservation_invoice_id' => $invoice->id]);
                }
                $redirectUrl = $request->input('redirect');
                $invoicesUrl = route('client.invoices');
                if (!$redirectUrl) {
                    $redirectUrl = $invoice->legal_case_id 
                        ? route('client.case.view', ['case' => $invoice->legal_case_id, 'tab' => 'invoices'])
                        : $invoicesUrl;
                }
                
                return response()->json([
                    'success' => true,
                    'redirect' => $redirectUrl,
                    'invoicesUrl' => $invoicesUrl,
                    'showSuccessModal' => true
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Payment failed. Please try again.'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Exception processing card payment', [
                'invoice_id' => $invoiceId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing your payment: ' . $e->getMessage()
            ]);
        }
    }
}
