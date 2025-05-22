<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\API\DistributorController;

class AuthController extends Controller
{
    /**
     * 用户登录
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            
            // 先删除用户现有的所有token
            $user->tokens()->delete();
            
            // 创建新token
            $token = $user->createToken('auth_token');
            $plainTextToken = $token->plainTextToken;
            
            // 获取token ID (格式是 id|hash)
            $tokenId = explode('|', $plainTextToken)[0];
            
            // 保存当前令牌ID
            $user->current_token = $tokenId;
            $user->save();

            return response()->json([
                'status' => true,
                'message' => '登录成功',
                'data' => [
                    'token' => $plainTextToken,
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                        'expire_time' => $user->expire_time,
                    ]
                ]
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => '邮箱或密码错误',
            'data' => null
        ], 401);
    }

    /**
     * 用户注册
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'inviter_id' => 'nullable|integer|exists:distributors,id',
        ]);

        try {
            DB::beginTransaction();

            // 获取系统试用时间设置（分钟）
            $trialMinutes = (int) Setting::getValue('trial_minutes', 60);
            $expireTime = null;
            
            if ($trialMinutes > 0) {
                $expireTime = now()->addMinutes($trialMinutes);
            }

            // 创建用户
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'user',
                'expire_time' => $expireTime,
            ]);

            // 如果有邀请人ID，绑定分销关系
            if ($request->has('inviter_id') && $request->inviter_id) {
                DistributorController::bindDistributor($user, $request->inviter_id);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => '注册成功',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);

            return response()->json([
                'status' => false,
                'message' => '注册失败: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * 用户登出
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => true,
            'message' => '登出成功',
            'data' => null
        ]);
    }
} 