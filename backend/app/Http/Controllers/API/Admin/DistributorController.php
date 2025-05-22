<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\Distributor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DistributorController extends Controller
{
    /**
     * 获取分销商列表
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = Distributor::with('user');
        
        // 搜索功能
        if ($request->has('keyword') && !empty($request->keyword)) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('nickname', 'like', "%{$keyword}%")
                  ->orWhereHas('user', function ($subQ) use ($keyword) {
                      $subQ->where('name', 'like', "%{$keyword}%")
                           ->orWhere('email', 'like', "%{$keyword}%");
                  });
            });
        }
        
        // 分页
        $perPage = $request->per_page ?? 15;
        $distributors = $query->orderBy('id', 'desc')->paginate($perPage);
        
        return response()->json([
            'status' => true,
            'message' => '获取分销商列表成功',
            'data' => [
                'distributors' => $distributors
            ]
        ]);
    }
    
    /**
     * 添加分销商
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'merchant_id' => 'required|integer',
            'merchant_key' => 'required|string',
            'nickname' => 'required|string|max:50'
        ]);
        
        try {
            // 开启事务
            DB::beginTransaction();
            
            // 创建新用户
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'distributor',
                'expire_time' => now()->addYear()
            ]);
            
            // 创建分销商
            $distributor = Distributor::create([
                'user_id' => $user->id,
                'merchant_id' => $request->merchant_id,
                'merchant_key' => $request->merchant_key,
                'nickname' => $request->nickname,
                'status' => true
            ]);
            
            DB::commit();
            
            return response()->json([
                'status' => true,
                'message' => '添加分销商成功',
                'data' => [
                    'distributor' => $distributor->load('user')
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            
            return response()->json([
                'status' => false,
                'message' => '添加分销商失败: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
    
    /**
     * 修改分销商状态
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|boolean'
        ]);
        
        $distributor = Distributor::findOrFail($id);
        $distributor->status = $request->status;
        $distributor->save();
        
        return response()->json([
            'status' => true,
            'message' => '更新分销商状态成功',
            'data' => [
                'distributor' => $distributor
            ]
        ]);
    }
    
    /**
     * 删除分销商
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            // 开启事务
            DB::beginTransaction();
            
            $distributor = Distributor::findOrFail($id);
            
            // 获取该分销商对应的用户
            $user = $distributor->user;
            
            // 删除分销商
            $distributor->delete();
            
            // 更新用户角色为普通用户
            if ($user && $user->role === 'distributor') {
                $user->role = 'user';
                $user->save();
            }
            
            DB::commit();
            
            return response()->json([
                'status' => true,
                'message' => '删除分销商成功',
                'data' => null
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            
            return response()->json([
                'status' => false,
                'message' => '删除分销商失败: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
    
    /**
     * 更新分销商信息
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nickname' => 'required|string|max:50',
            'merchant_id' => 'required|integer',
            'merchant_key' => 'required|string',
            'user_name' => 'required|string|max:255',
            'password' => 'nullable|string|min:6',
        ]);
        
        try {
            // 开启事务
            DB::beginTransaction();
            
            $distributor = Distributor::with('user')->findOrFail($id);
            
            // 更新分销商信息
            $distributor->nickname = $request->nickname;
            $distributor->merchant_id = $request->merchant_id;
            $distributor->merchant_key = $request->merchant_key;
            $distributor->save();
            
            // 更新用户信息
            $user = $distributor->user;
            $user->name = $request->user_name;
            
            // 如果提供了密码，则更新密码
            if ($request->has('password') && !empty($request->password)) {
                $user->password = Hash::make($request->password);
            }
            
            $user->save();
            
            DB::commit();
            
            return response()->json([
                'status' => true,
                'message' => '更新分销商信息成功',
                'data' => [
                    'distributor' => $distributor->load('user')
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            
            return response()->json([
                'status' => false,
                'message' => '更新分销商信息失败: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
    
    /**
     * 搜索用户（用于选择用户为分销商）
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchUsers(Request $request)
    {
        $request->validate([
            'keyword' => 'required|string|min:1'
        ]);
        
        $keyword = $request->keyword;
        
        $users = User::where(function ($query) use ($keyword) {
                $query->where('name', 'like', "%{$keyword}%")
                      ->orWhere('email', 'like', "%{$keyword}%");
            })
            ->whereNotIn('id', function ($query) {
                $query->select('user_id')->from('distributors');
            })
            ->limit(10)
            ->get(['id', 'name', 'email']);
        
        return response()->json([
            'status' => true,
            'message' => '搜索用户成功',
            'data' => [
                'users' => $users
            ]
        ]);
    }
} 