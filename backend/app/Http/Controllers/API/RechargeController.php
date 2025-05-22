<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RechargeController extends Controller
{
    protected $paymentService;
    
    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }
    
    /**
     * 获取充值套餐列表
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPlans()
    {
        $plans = $this->paymentService->getAvailablePlans();
        
        return response()->json([
            'status' => true,
            'message' => '获取充值套餐成功',
            'data' => [
                'plans' => array_values($plans)
            ]
        ]);
    }

    /**
     * 处理充值请求
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function recharge(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|integer',
            'payment_method' => 'required|string'
        ]);

        $planId = $request->input('plan_id');
        $paymentMethod = $request->input('payment_method');
        
        try {
            $result = $this->paymentService->createPayment($planId, $paymentMethod);
            
            return response()->json([
                'status' => true,
                'message' => '创建充值订单成功',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('创建充值订单失败', ['error' => $e->getMessage()]);
            
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
} 