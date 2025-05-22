<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * 获取用户信息
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $user = User::find($id);
        
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => '用户不存在',
                'data' => null
            ], 404);
        }
        
        return response()->json([
            'status' => true,
            'message' => '获取用户信息成功',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role ?? 'user',
                    'created_at' => $user->created_at
                ]
            ]
        ]);
    }
    
    /**
     * 获取当前登录用户的个人信息
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function profile(Request $request)
    {
        $user = Auth::user();
        
        return response()->json([
            'status' => true,
            'message' => '获取个人信息成功',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role ?? 'user',
                    'expire_time' => $user->expire_time,
                    'created_at' => $user->created_at
                ]
            ]
        ]);
    }
} 