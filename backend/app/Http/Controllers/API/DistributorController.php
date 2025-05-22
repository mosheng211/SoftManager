<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Distributor;
use App\Models\User;
use App\Models\RechargeRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DistributorController extends Controller
{
    /**
     * 获取分销商邀请的用户列表
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getInvitedUsers()
    {
        $distributor = Auth::user()->distributor;
        
        if (!$distributor) {
            return response()->json([
                'status' => false,
                'message' => '您不是分销商',
                'data' => null
            ], 403);
        }
        
        // 获取分销商邀请的用户并加载充值记录
        $users = $distributor->invitedUsers()
            ->with('rechargeRecords')
            ->get()
            ->map(function ($user) {
                // 计算用户的累计充值金额
                $totalRecharge = $user->rechargeRecords()
                    ->where('status', 'success')
                    ->sum('amount');
                
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'created_at' => $user->created_at,
                    'expire_time' => $user->expire_time,
                    'total_recharge' => (float) $totalRecharge
                ];
            });
        
        return response()->json([
            'status' => true,
            'message' => '获取邀请用户列表成功',
            'data' => [
                'users' => $users
            ]
        ]);
    }
    
    /**
     * 获取分销商资料
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProfile()
    {
        $user = Auth::user();
        $distributor = $user->distributor;
        
        if (!$distributor) {
            return response()->json([
                'status' => false,
                'message' => '您不是分销商',
                'data' => null
            ], 403);
        }
        
        return response()->json([
            'status' => true,
            'message' => '获取分销商资料成功',
            'data' => [
                'distributor' => $distributor
            ]
        ]);
    }
    
    /**
     * 为用户注册绑定分销商关系
     *
     * @param User $user
     * @param int $distributorId
     * @return bool
     */
    public static function bindDistributor(User $user, $distributorId)
    {
        try {
            $distributor = Distributor::where('id', $distributorId)
                ->where('status', true)
                ->first();
            
            if (!$distributor) {
                return false;
            }
            
            // 如果用户已经绑定了分销商，则不重复绑定
            if ($user->inviter()->exists()) {
                return false;
            }
            
            $user->inviter()->attach($distributor->id);
            return true;
        } catch (\Exception $e) {
            report($e);
            return false;
        }
    }
} 