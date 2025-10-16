<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayMongoService
{
    protected string $baseUrl;
    protected string $secretKey;
    protected string $publicKey;
    protected ?string $webhookSecret;

    public function __construct()
    {
        $this->baseUrl = config('services.paymongo.base_url', 'https://api.paymongo.com/v1');
        $this->secretKey = config('services.paymongo.secret_key');
        $this->publicKey = config('services.paymongo.public_key');
        $this->webhookSecret = config('services.paymongo.webhook_secret');
    }

    /**
     * Create a payment intent
     */
    public function createPaymentIntent(Invoice $invoice): array
    {
        try {
            $amountToCharge = $invoice->payment_plan !== Invoice::PAYMENT_PLAN_FULL 
                ? $invoice->getInstallmentAmount() 
                : $invoice->total;

            $response = Http::withBasicAuth($this->secretKey, '')
                ->post("{$this->baseUrl}/payment_intents", [
                    'data' => [
                        'attributes' => [
                            'amount' => (int)($amountToCharge * 100), // Convert to cents
                            'payment_method_allowed' => [
                                'card', 'gcash'
                            ],
                            'payment_method_options' => [
                                'card' => [
                                    'request_three_d_secure' => 'automatic'
                                ]
                            ],
                            'currency' => 'PHP',
                            'description' => "Payment for Invoice #{$invoice->invoice_number}" . 
                                             ($invoice->payment_plan !== Invoice::PAYMENT_PLAN_FULL ? " (Installment)" : ""),
                            'statement_descriptor' => 'LEXCAV LEGAL'
                        ]
                    ]
                ]);

            $data = $response->json();
            
            if ($response->successful() && isset($data['data']['id'])) {
                $invoice->paymongo_payment_intent_id = $data['data']['id'];
                $invoice->save();
                
                return [
                    'success' => true,
                    'intent_id' => $data['data']['id'],
                    'client_key' => $data['data']['attributes']['client_key'],
                    'data' => $data
                ];
            }
            
            Log::error('PayMongo payment intent creation failed', [
                'invoice_id' => $invoice->id,
                'response' => $data
            ]);
            
            return [
                'success' => false,
                'message' => $data['errors'][0]['detail'] ?? 'Failed to create payment intent',
                'data' => $data
            ];
        } catch (\Exception $e) {
            Log::error('PayMongo payment intent exception', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'An error occurred while processing the payment',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Create a payment source (like GCash)
     * @param Invoice $invoice
     * @param string $type
     * @param string|null $successUrl
     * @param string|null $failedUrl
     * @return array
     */
    public function createSource(Invoice $invoice, string $type = 'gcash', string $successUrl = null, string $failedUrl = null): array
    {
        try {
            $amountToCharge = $invoice->payment_plan !== Invoice::PAYMENT_PLAN_FULL 
                ? $invoice->getInstallmentAmount() 
                : $invoice->total;

            $redirect = [
                'success' => $successUrl ?: route('payment.success', ['invoice' => $invoice->id]),
                'failed' => $failedUrl ?: route('payment.failed', ['invoice' => $invoice->id]),
            ];

            $response = Http::withBasicAuth($this->secretKey, '')
                ->post("{$this->baseUrl}/sources", [
                    'data' => [
                        'attributes' => [
                            'amount' => (int)($amountToCharge * 100), // Convert to cents
                            'redirect' => $redirect,
                            'type' => $type,
                            'currency' => 'PHP',
                            'billing' => [
                                'name' => $this->getClientFullName($invoice->client),
                                'email' => $invoice->client->email,
                            ],
                            'description' => "Payment for Invoice #{$invoice->invoice_number}" . 
                                             ($invoice->payment_plan !== Invoice::PAYMENT_PLAN_FULL ? " (Installment)" : "")
                        ]
                    ]
                ]);

            $data = $response->json();
            
            if ($response->successful() && isset($data['data']['id'])) {
                $invoice->paymongo_source_id = $data['data']['id'];
                $invoice->payment_link = $data['data']['attributes']['redirect']['checkout_url'];
                $invoice->save();
                
                return [
                    'success' => true,
                    'source_id' => $data['data']['id'],
                    'checkout_url' => $data['data']['attributes']['redirect']['checkout_url'],
                    'data' => $data
                ];
            }
            
            Log::error('PayMongo source creation failed', [
                'invoice_id' => $invoice->id,
                'response' => $data
            ]);
            
            return [
                'success' => false,
                'message' => $data['errors'][0]['detail'] ?? 'Failed to create payment source',
                'data' => $data
            ];
        } catch (\Exception $e) {
            Log::error('PayMongo source exception', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'An error occurred while creating the payment source',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Process a webhook event from PayMongo
     */
    public function processWebhook(array $payload): array
    {
        try {
            $eventType = $payload['data']['attributes']['type'] ?? null;
            $resourceId = $payload['data']['attributes']['data']['id'] ?? null;
            
            if (!$eventType || !$resourceId) {
                return [
                    'success' => false,
                    'message' => 'Invalid webhook payload'
                ];
            }
            
            switch ($eventType) {
                case 'source.chargeable':
                    return $this->processSourceChargeable($resourceId, $payload);
                
                case 'payment.paid':
                    return $this->processPaymentPaid($resourceId, $payload);
                
                case 'payment.failed':
                    return $this->processPaymentFailed($resourceId, $payload);
                
                default:
                    return [
                        'success' => true,
                        'message' => 'Event type not processed',
                        'event_type' => $eventType
                    ];
            }
        } catch (\Exception $e) {
            Log::error('PayMongo webhook processing exception', [
                'error' => $e->getMessage(),
                'payload' => $payload
            ]);
            
            return [
                'success' => false,
                'message' => 'An error occurred while processing the webhook',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Process a source.chargeable event
     */
    protected function processSourceChargeable(string $sourceId, array $payload): array
    {
        $invoice = Invoice::where('paymongo_source_id', $sourceId)->first();
        
        if (!$invoice) {
            return [
                'success' => false,
                'message' => 'Invoice not found for source ID'
            ];
        }
        
        $amountToCharge = $invoice->payment_plan !== Invoice::PAYMENT_PLAN_FULL 
            ? $invoice->getInstallmentAmount() 
            : $invoice->total;

        // Create a payment using the source
        $response = Http::withBasicAuth($this->secretKey, '')
            ->post("{$this->baseUrl}/payments", [
                'data' => [
                    'attributes' => [
                        'amount' => (int)($amountToCharge * 100), // Convert to cents
                        'source' => [
                            'id' => $sourceId,
                            'type' => 'source'
                        ],
                        'currency' => 'PHP',
                        'description' => "Payment for Invoice #{$invoice->invoice_number}" . 
                                         ($invoice->payment_plan !== Invoice::PAYMENT_PLAN_FULL ? " (Installment)" : "")
                    ]
                ]
            ]);
        
        $data = $response->json();
        
        if ($response->successful() && isset($data['data']['id'])) {
            $invoice->paymongo_payment_id = $data['data']['id'];
            $invoice->save();
            
            return [
                'success' => true,
                'message' => 'Payment created successfully',
                'payment_id' => $data['data']['id']
            ];
        }
        
        return [
            'success' => false,
            'message' => $data['errors'][0]['detail'] ?? 'Failed to create payment',
            'data' => $data
        ];
    }

    /**
     * Process a payment.paid event
     */
    protected function processPaymentPaid(string $paymentId, array $payload): array
    {
        $invoice = Invoice::where('paymongo_payment_id', $paymentId)
            ->orWhere('paymongo_payment_intent_id', $paymentId)
            ->first();
        
        if (!$invoice) {
            return [
                'success' => false,
                'message' => 'Invoice not found for payment ID'
            ];
        }
        
        // Get payment details
        $attributes = $payload['data']['attributes']['data']['attributes'] ?? [];
        $amount = ($attributes['amount'] ?? 0) / 100; // Convert from cents
        $paymentMethod = $attributes['source']['type'] ?? 'unknown';
        
        // Create payment record
        $payment = Payment::create([
            'invoice_id' => $invoice->id,
            'client_id' => $invoice->client_id,
            'amount' => $amount,
            'payment_method' => $paymentMethod,
            'transaction_id' => $paymentId,
            'status' => Payment::STATUS_SUCCESS,
            'payment_details' => $attributes,
            'payment_date' => now(),
            'paymongo_payment_id' => $paymentId
        ]);
        
        // Update invoice status
        $invoice->updateStatus();
        
        // Notify the lawyer
        NotificationService::paymentReceived($invoice, $payment);
        
        return [
            'success' => true,
            'message' => 'Payment recorded successfully'
        ];
    }

    /**
     * Process a payment.failed event
     */
    protected function processPaymentFailed(string $paymentId, array $payload): array
    {
        $invoice = Invoice::where('paymongo_payment_id', $paymentId)
            ->orWhere('paymongo_payment_intent_id', $paymentId)
            ->first();
        
        if (!$invoice) {
            return [
                'success' => false,
                'message' => 'Invoice not found for payment ID'
            ];
        }
        
        // Get payment details
        $attributes = $payload['data']['attributes']['data']['attributes'] ?? [];
        $amount = ($attributes['amount'] ?? 0) / 100; // Convert from cents
        $paymentMethod = $attributes['source']['type'] ?? 'unknown';
        
        // Create payment record
        Payment::create([
            'invoice_id' => $invoice->id,
            'client_id' => $invoice->client_id,
            'amount' => $amount,
            'payment_method' => $paymentMethod,
            'transaction_id' => $paymentId,
            'status' => Payment::STATUS_FAILED,
            'payment_details' => $attributes,
            'payment_date' => now(),
            'paymongo_payment_id' => $paymentId
        ]);
        
        return [
            'success' => true,
            'message' => 'Failed payment recorded'
        ];
    }

    /**
     * Check the status of a payment source
     */
    public function checkSourceStatus(string $sourceId): array
    {
        try {
            $response = Http::withBasicAuth($this->secretKey, '')
                ->get("{$this->baseUrl}/sources/{$sourceId}");

            $data = $response->json();
            
            if ($response->successful() && isset($data['data']['id'])) {
                return [
                    'success' => true,
                    'status' => $data['data']['attributes']['status'] ?? 'unknown',
                    'data' => $data
                ];
            }
            
            Log::error('PayMongo source check failed', [
                'source_id' => $sourceId,
                'response' => $data
            ]);
            
            return [
                'success' => false,
                'message' => $data['errors'][0]['detail'] ?? 'Failed to check source status',
                'data' => $data
            ];
        } catch (\Exception $e) {
            Log::error('PayMongo source check exception', [
                'source_id' => $sourceId,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'An error occurred while checking the source status',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Create a payment from a source (manual version of processSourceChargeable)
     */
    public function createPaymentFromSource(Invoice $invoice, string $sourceId): array
    {
        try {
            Log::info('Creating payment from source', ['invoice_id' => $invoice->id, 'source_id' => $sourceId]);
            
            $amountToCharge = $invoice->payment_plan !== Invoice::PAYMENT_PLAN_FULL 
                ? $invoice->getInstallmentAmount() 
                : $invoice->total;

            $response = Http::withBasicAuth($this->secretKey, '')
                ->post("{$this->baseUrl}/payments", [
                    'data' => [
                        'attributes' => [
                            'amount' => (int)($amountToCharge * 100), // amount in cents
                            'source' => [
                                'id' => $sourceId,
                                'type' => 'source',
                            ],
                            'currency' => 'PHP',
                            'description' => "Payment for Invoice #{$invoice->invoice_number}" . 
                                             ($invoice->payment_plan !== Invoice::PAYMENT_PLAN_FULL ? " (Installment)" : ""),
                            'statement_descriptor' => 'LEXCAV PAYMENT',
                        ],
                    ],
                ]);
            $paymentData = $response->json();
            
            if ($response->successful() && isset($paymentData['data']['id'])) {
                $paymentId = $paymentData['data']['id'];
                $invoice->paymongo_payment_id = $paymentId;
                $invoice->save();
                
                // Create payment record in our database
                $attributes = $paymentData['data']['attributes'] ?? [];
                $amount = ($attributes['amount'] ?? 0) / 100; // Convert from cents
                $paymentMethod = $attributes['source']['type'] ?? 'unknown';
                
                $payment = Payment::create([
                    'invoice_id' => $invoice->id,
                    'client_id' => $invoice->client_id,
                    'amount' => $amount,
                    'payment_method' => $paymentMethod,
                    'transaction_id' => $paymentId,
                    'status' => Payment::STATUS_SUCCESS,
                    'payment_details' => $attributes,
                    'payment_date' => now(),
                    'paymongo_payment_id' => $paymentId
                ]);
                
                // Update invoice status
                $invoice->updateStatus();
                
                // Notify the lawyer
                NotificationService::paymentReceived($invoice, $payment);
                
                return [
                    'success' => true,
                    'message' => 'Payment created successfully',
                    'payment_id' => $paymentId
                ];
            }
            
            Log::error('Manual payment creation failed', [
                'invoice_id' => $invoice->id,
                'source_id' => $sourceId,
                'response' => $paymentData
            ]);
            
            return [
                'success' => false,
                'message' => $paymentData['errors'][0]['detail'] ?? 'Failed to create payment',
                'data' => $paymentData
            ];
        } catch (\Exception $e) {
            Log::error('Manual payment creation exception', [
                'invoice_id' => $invoice->id,
                'source_id' => $sourceId,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'An error occurred while creating the payment',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Attach a payment method to a payment intent
     */
    public function attachPaymentMethod(string $paymentIntentId, string $paymentMethodId): array
    {
        try {
            $response = Http::withBasicAuth($this->secretKey, '')
                ->post("{$this->baseUrl}/payment_intents/{$paymentIntentId}/attach", [
                    'data' => [
                        'attributes' => [
                            'payment_method' => $paymentMethodId
                        ]
                    ]
                ]);

            $data = $response->json();
            
            if ($response->successful() && isset($data['data']['id'])) {
                $status = $data['data']['attributes']['status'] ?? '';
                
                return [
                    'success' => true,
                    'status' => $status,
                    'data' => $data
                ];
            }
            
            Log::error('PayMongo payment method attachment failed', [
                'payment_intent_id' => $paymentIntentId,
                'payment_method_id' => $paymentMethodId,
                'response' => $data
            ]);
            
            return [
                'success' => false,
                'message' => $data['errors'][0]['detail'] ?? 'Failed to attach payment method',
                'data' => $data
            ];
        } catch (\Exception $e) {
            Log::error('PayMongo payment method attachment exception', [
                'payment_intent_id' => $paymentIntentId,
                'payment_method_id' => $paymentMethodId,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'An error occurred while attaching the payment method',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get the client's full name from their profile
     */
    private function getClientFullName($client)
    {
        // For clients with a client profile
        if ($client->clientProfile) {
            return trim($client->clientProfile->first_name . ' ' . $client->clientProfile->last_name);
        }

        // Fallback to client name
        return $client->name ?: 'LexCav Client';
    }
} 