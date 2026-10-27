<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_no')->unique();
            $table->foreignId('client_id')->constrained('users');
            $table->foreignId('vendor_id')->constrained('vendors');
            $table->foreignId('assigned_user_id')->constrained('users');
            $table->date('order_date');
            $table->date('vendor_expected_date');
            $table->date('vendor_received_date')->nullable();
            $table->date('client_delivery_date');
            $table->date('client_delivered_date')->nullable();
            $table->enum('purchase_status', ['Draft', 'Ordered', 'Partially Received', 'Received'])->default('Draft');
            $table->enum('delivery_status', ['Pending', 'Ready to Dispatch', 'Dispatched', 'Partially Delivered', 'Delivered'])->default('Pending');
            $table->enum('overall_status', ['Open', 'Completed', 'Cancelled'])->default('Open');
            $table->boolean('is_on_hold')->default(false);
            $table->enum('priority', ['Normal', 'High'])->default('Normal');
            $table->longText('notes')->nullable();
            $table->text('cancel_reason')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['vendor_expected_date', 'purchase_status']);
            $table->index(['client_delivery_date', 'delivery_status']);
            $table->index(['assigned_user_id', 'overall_status']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('product_name');
            $table->decimal('quantity', 14, 3);
            $table->string('unit', 30)->nullable();
            $table->decimal('qty_received', 14, 3)->default(0);
            $table->decimal('qty_delivered', 14, 3)->default(0);
            $table->decimal('purchase_rate', 14, 2)->nullable();
            $table->decimal('selling_rate', 14, 2)->nullable();
            $table->string('remarks')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('order_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->enum('track', ['Purchase', 'Delivery', 'Order']);
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->foreignId('changed_by')->constrained('users');
            $table->timestamp('changed_at');
            $table->text('remark')->nullable();
            $table->json('meta')->nullable();
            $table->index(['order_id', 'changed_at']);
        });

        Schema::create('order_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('type')->default('Other');
            $table->string('file_path');
            $table->string('original_name');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->foreignId('uploaded_by')->constrained('users');
            $table->timestamps();
        });

        Schema::create('order_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->string('reminder_type');
            $table->date('reminder_for_date');
            $table->date('sent_on');
            $table->enum('channel', ['in_app', 'email'])->default('in_app');
            $table->enum('status', ['Sent', 'Failed'])->default('Sent');
            $table->unique(['order_id', 'reminder_type', 'user_id', 'sent_on'], 'order_reminder_dedupe');
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('order_reminders');
        Schema::dropIfExists('order_attachments');
        Schema::dropIfExists('order_status_history');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
