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
        Schema::create('recharge_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2)->comment('充值金额');
            $table->integer('days')->comment('充值天数');
            $table->timestamp('paid_at')->nullable()->comment('支付时间');
            $table->string('order_no')->unique()->comment('订单号');
            $table->string('payment_method')->comment('支付方式：alipay, wechat');
            $table->string('status')->default('pending')->comment('状态：pending, success, failed');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recharge_records');
    }
}; 