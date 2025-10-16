<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade'); // Assuming client is a user
            $table->decimal('amount', 10, 2);
            $table->string('payment_method'); // e.g., 'gcash', 'card', 'cash'
            $table->string('transaction_id')->nullable(); // From PayMongo or manual entry
            $table->enum('status', ['pending', 'success', 'failed', 'refunded', 'cancelled'])->default('pending');
            $table->timestamp('payment_date')->nullable();
            $table->string('receipt_path')->nullable(); // For manually uploaded receipts
            $table->text('payment_notes')->nullable(); // Internal notes about the payment
            $table->json('payment_details')->nullable(); // Store additional details, like PayMongo response
            
            // PayMongo specific fields, if needed beyond transaction_id
            $table->string('paymongo_payment_id')->nullable()->unique(); // PayMongo's payment ID
            // $table->string('paymongo_source_id')->nullable(); // Already in invoices table if using sources

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
