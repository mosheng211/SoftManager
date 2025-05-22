<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    /**
     * 获取支付方式列表
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPaymentMethods()
    {
        $methods = PaymentMethod::orderBy('sort_order')->get();
        
        return response()->json([
            'status' => true,
            'message' => '获取支付方式列表成功',
            'data' => [
                'methods' => $methods
            ]
        ]);
    }
    
    /**
     * 获取支付设置
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPaymentSettings()
    {
        $settings = [
            'payment_gateway_url' => Setting::getValue('payment_gateway_url', 'https://pay.msblog.cc/submit.php'),
        ];
        
        return response()->json([
            'status' => true,
            'message' => '获取支付设置成功',
            'data' => [
                'settings' => $settings
            ]
        ]);
    }
    
    /**
     * 更新支付方式状态
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePaymentMethodStatus(Request $request, $id)
    {
        $request->validate([
            'is_enabled' => 'required|boolean'
        ]);
        
        $method = PaymentMethod::findOrFail($id);
        $method->is_enabled = $request->is_enabled;
        $method->save();
        
        return response()->json([
            'status' => true,
            'message' => '更新支付方式状态成功',
            'data' => $method
        ]);
    }
    
    /**
     * 更新支付设置
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePaymentSettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payment_gateway_url' => 'required|url',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }
        
        // 更新设置
        Setting::setValue('payment_gateway_url', $request->payment_gateway_url);
        
        return response()->json([
            'status' => true,
            'message' => '更新支付设置成功',
            'data' => null
        ]);
    }
    
    /**
     * 更新支付方式排序
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePaymentMethodOrders(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'orders' => 'required|array',
            'orders.*.id' => 'required|integer|exists:payment_methods,id',
            'orders.*.sort_order' => 'required|integer|min:0'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }
        
        foreach ($request->orders as $order) {
            PaymentMethod::where('id', $order['id'])->update(['sort_order' => $order['sort_order']]);
        }
        
        return response()->json([
            'status' => true,
            'message' => '更新支付方式排序成功',
            'data' => null
        ]);
    }
}
