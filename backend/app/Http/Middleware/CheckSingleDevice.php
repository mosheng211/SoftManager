<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckSingleDevice
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 检查是否允许多设备登录
        $allowMultiDevice = Setting::getValue('allow_multi_device_login', '0');
        
        // 如果允许多设备登录，则直接通过
        if ($allowMultiDevice === '1') {
            return $next($request);
        }
        
        // 获取用户和当前令牌
        $user = $request->user();
        $bearer = $request->bearerToken();
        
        // 如果用户存在且有当前令牌设置
        if ($user && $user->current_token && $bearer) {
            // 截取令牌部分 (Sanctum令牌格式是数字|hash)
            $tokenId = explode('|', $bearer)[0] ?? '';
            
            // 比较当前令牌与用户存储的令牌
            if ($user->current_token !== $tokenId) {
                // 令牌不匹配，用户已在其他设备登录
                return response()->json([
                    'status' => false,
                    'message' => '您已在其他设备登录',
                    'code' => 'device_changed',
                    'data' => null
                ], 401);
            }
        }

        return $next($request);
    }
}
