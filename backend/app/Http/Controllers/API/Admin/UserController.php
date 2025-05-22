<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Distributor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * 获取用户列表
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = User::query();
        
        // 搜索功能
        if ($request->has('keyword') && !empty($request->keyword)) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('email', 'like', "%{$keyword}%");
            });
        }
        
        // 分页
        $perPage = $request->per_page ?? 15;
        $users = $query->orderBy('id', 'desc')->paginate($perPage);
        
        // 处理返回数据，添加邀请人信息
        $users->getCollection()->transform(function ($user) {
            $inviter = $user->inviter()->with('user')->first();
            
            $userData = $user->toArray();
            $userData['inviter_id'] = $inviter ? $inviter->id : null;
            $userData['inviter_nickname'] = $inviter ? $inviter->nickname : null;
            
            return $userData;
        });
        
        return response()->json([
            'status' => true,
            'message' => '获取用户列表成功',
            'data' => [
                'users' => $users
            ]
        ]);
    }
    
    /**
     * 更新用户到期时间
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateExpireTime(Request $request, $id)
    {
        $request->validate([
            'expire_time' => 'required|date'
        ]);
        
        $user = User::findOrFail($id);
        $user->expire_time = $request->expire_time;
        $user->save();
        
        return response()->json([
            'status' => true,
            'message' => '更新用户到期时间成功',
            'data' => [
                'user' => $user
            ]
        ]);
    }
    
    /**
     * 更新用户密码
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePassword(Request $request, $id)
    {
        $request->validate([
            'password' => 'required|string|min:6',
        ]);
        
        $user = User::findOrFail($id);
        $user->password = Hash::make($request->password);
        $user->save();
        
        return response()->json([
            'status' => true,
            'message' => '更新用户密码成功',
            'data' => null
        ]);
    }
} 