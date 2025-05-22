<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected $paymentService;
    
    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }
    
    /**
     * 获取可用的支付方式
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPaymentMethods()
    {
        $methods = $this->paymentService->getEnabledPaymentMethods();
        
        return response()->json([
            'status' => true,
            'message' => '获取支付方式成功',
            'data' => [
                'methods' => $methods
            ]
        ]);
    }
    
    /**
     * 创建支付订单
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createPayment(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|integer',
            'payment_method' => 'required|string'
        ]);
        
        try {
            $result = $this->paymentService->createPayment(
                $request->input('plan_id'),
                $request->input('payment_method')
            );
            
            return response()->json([
                'status' => true,
                'message' => '创建支付订单成功',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('创建支付订单失败', ['error' => $e->getMessage()]);
            
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
    
    /**
     * 处理支付异步通知
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function handleNotify(Request $request)
    {
        Log::info('收到支付回调', $request->all());
        
        // 验证签名
        if (!$this->paymentService->verifyCallback($request->all())) {
            Log::error('支付回调验证失败');
            return response('fail', 400);
        }
        
        // 处理支付成功回调
        if ($this->paymentService->handlePaymentCallback($request->all())) {
            return response('success');
        }
        
        return response('fail', 400);
    }
    
    /**
     * 处理支付同步跳转
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleReturn(Request $request)
    {
        Log::info('收到支付跳转', $request->all());
        
        // 验证签名
        if (!$this->paymentService->verifyCallback($request->all())) {
            return redirect('/user-center')->with('error', '支付验证失败');
        }
        
        return redirect('/user-center')->with('success', '支付成功');
    }
}
