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
        // 由于我们已经有settings表，我们将使用该表存储套餐价格
        // 添加默认套餐价格设置
        DB::table('settings')->insert([
            [
                'key' => 'monthly_plan_price',
                'value' => '39.9', // 月卡默认价格
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'quarterly_plan_price',
                'value' => '99.9', // 季卡默认价格
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'yearly_plan_price',
                'value' => '299.9', // 年卡默认价格
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
        // 删除套餐价格设置
        DB::table('settings')->whereIn('key', [
            'monthly_plan_price',
            'quarterly_plan_price',
            'yearly_plan_price'
        ])->delete();
    }
};
