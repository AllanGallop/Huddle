<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->date('due_date')->nullable()->after('project_status');
            $table->string('financial_status')->nullable()->after('due_date');
            $table->decimal('quote_amount', 12, 2)->nullable()->after('financial_status');
            $table->decimal('invoice_amount', 12, 2)->nullable()->after('quote_amount');
            $table->decimal('deposit_amount', 12, 2)->nullable()->after('invoice_amount');
            $table->decimal('payment_amount', 12, 2)->nullable()->after('deposit_amount');
            $table->text('quote_notes')->nullable()->after('payment_amount');
            $table->text('invoice_notes')->nullable()->after('quote_notes');
            $table->timestamp('quoted_at')->nullable()->after('invoice_notes');
            $table->timestamp('invoiced_at')->nullable()->after('quoted_at');
            $table->timestamp('deposit_paid_at')->nullable()->after('invoiced_at');
            $table->timestamp('paid_at')->nullable()->after('deposit_paid_at');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'due_date',
                'financial_status',
                'quote_amount',
                'invoice_amount',
                'deposit_amount',
                'payment_amount',
                'quote_notes',
                'invoice_notes',
                'quoted_at',
                'invoiced_at',
                'deposit_paid_at',
                'paid_at',
            ]);
        });
    }
};
