<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * 获取系统设置
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $settings = [
            'trial_minutes' => (int) Setting::getValue('trial_minutes', 60),
            'monthly_plan_price' => (float) Setting::getValue('monthly_plan_price', 39.9),
            'quarterly_plan_price' => (float) Setting::getValue('quarterly_plan_price', 99.9),
            'yearly_plan_price' => (float) Setting::getValue('yearly_plan_price', 299.9),
            'allow_multi_device_login' => (bool) Setting::getValue('allow_multi_device_login', '0')
        ];
        
        return response()->json([
            'status' => true,
            'message' => '获取系统设置成功',
            'data' => [
                'settings' => $settings
            ]
        ]);
    }
    
    /**
     * 更新系统设置
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $request->validate([
            'trial_minutes' => 'required|integer|min:0',
            'monthly_plan_price' => 'required|numeric|min:0',
            'quarterly_plan_price' => 'required|numeric|min:0',
            'yearly_plan_price' => 'required|numeric|min:0',
            'allow_multi_device_login' => 'required|boolean'
        ]);
        
        Setting::setValue('trial_minutes', $request->trial_minutes);
        Setting::setValue('monthly_plan_price', $request->monthly_plan_price);
        Setting::setValue('quarterly_plan_price', $request->quarterly_plan_price);
        Setting::setValue('yearly_plan_price', $request->yearly_plan_price);
        Setting::setValue('allow_multi_device_login', $request->allow_multi_device_login ? '1' : '0');
        
        return response()->json([
            'status' => true,
            'message' => '更新系统设置成功',
            'data' => null
        ]);
    }
} 