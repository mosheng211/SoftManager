<?php
// 加载Laravel环境
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// 确认数据库连接
try {
    // 创建管理员用户
    $admin = new App\Models\User();
    $admin->name = '管理员';
    $admin->email = 'admin@example.com';
    $admin->password = bcrypt('password');
    $admin->role = 'admin';
    $admin->save();
    
    echo "管理员用户创建成功\n";
    
    // 创建普通用户
    $user = new App\Models\User();
    $user->name = '测试用户';
    $user->email = 'user@example.com';
    $user->password = bcrypt('password');
    $user->role = 'user';
    $user->save();
    
    echo "普通用户创建成功\n";
    
} catch (Exception $e) {
    echo "错误: " . $e->getMessage() . "\n";
} 