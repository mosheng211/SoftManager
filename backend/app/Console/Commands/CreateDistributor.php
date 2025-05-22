<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Distributor;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class CreateDistributor extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'distributor:create {name} {email} {password} {nickname}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '创建一个新的分销商';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            DB::beginTransaction();
            
            // 创建用户
            $user = User::create([
                'name' => $this->argument('name'),
                'email' => $this->argument('email'),
                'password' => Hash::make($this->argument('password')),
                'role' => 'distributor',
                'expire_time' => now()->addYear()
            ]);
            
            // 创建分销商
            $distributor = Distributor::create([
                'user_id' => $user->id,
                'nickname' => $this->argument('nickname'),
                'status' => true
            ]);
            
            DB::commit();
            
            $this->info('分销商创建成功!');
            $this->line('用户ID: ' . $user->id);
            $this->line('分销商ID: ' . $distributor->id);
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('创建分销商失败: ' . $e->getMessage());
            
            return Command::FAILURE;
        }
    }
} 