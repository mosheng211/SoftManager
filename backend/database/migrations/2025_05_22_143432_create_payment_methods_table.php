<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->comment('支付方式名称');
            $table->string('code', 20)->unique()->comment('支付方式代码');
            $table->text('description')->nullable()->comment('描述');
            $table->boolean('is_enabled')->default(false)->comment('是否启用');
            $table->integer('sort_order')->default(0)->comment('排序');
            $table->timestamps();
        });
        
        // 添加默认支付方式
        DB::table('payment_methods')->insert([
            [
                'name' => '支付宝',
                'code' => 'alipay',
                'description' => '使用支付宝支付',
                'is_enabled' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => '微信支付',
                'code' => 'wxpay',
                'description' => '使用微信支付',
                'is_enabled' => false,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'QQ钱包',
                'code' => 'qqpay',
                'description' => '使用QQ钱包支付',
                'is_enabled' => false,
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => '网银支付',
                'code' => 'bank',
                'description' => '使用网银支付',
                'is_enabled' => false,
                'sort_order' => 4,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => '京东支付',
                'code' => 'jdpay',
                'description' => '使用京东支付',
                'is_enabled' => false,
                'sort_order' => 5,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'PayPal',
                'code' => 'paypal',
                'description' => '使用PayPal支付',
                'is_enabled' => false,
                'sort_order' => 6,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
        
        // 添加支付网关设置到settings表
        Schema::table('settings', function (Blueprint $table) {
            // 确保settings表存在
            if (!Schema::hasTable('settings')) {
                return;
            }
        });
        
        // 添加支付网关配置
        DB::table('settings')->insert([
            [
                'key' => 'payment_gateway_url',
                'value' => 'https://pay.msblog.cc/submit.php',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'payment_merchant_id',
                'value' => '1000', // 默认商户ID
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'payment_merchant_key',
                'value' => '', // 默认为空，需要在管理后台设置
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
        
        // 删除支付网关配置
        DB::table('settings')->whereIn('key', [
            'payment_gateway_url',
            'payment_merchant_id',
            'payment_merchant_key'
        ])->delete();
    }
};
