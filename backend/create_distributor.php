<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Distributor;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

try {
    DB::beginTransaction();
    
    // 创建用户
    $user = User::create([
        'name' => 'Distributor1',
        'email' => 'distributor1_' . time() . '@example.com',
        'password' => Hash::make('password123'),
        'role' => 'distributor',
        'expire_time' => now()->addYear()
    ]);
    
    // 创建分销商
    $distributor = Distributor::create([
        'user_id' => $user->id,
        'nickname' => 'Test Distributor',
        'status' => true
    ]);
    
    DB::commit();
    
    echo "分销商创建成功!\n";
    echo "用户ID: " . $user->id . "\n";
    echo "分销商ID: " . $distributor->id . "\n";
} catch (\Exception $e) {
    DB::rollBack();
    echo "创建分销商失败: " . $e->getMessage() . "\n";
} 