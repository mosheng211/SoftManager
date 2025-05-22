<?php

namespace App\Services;

use App\Models\PaymentMethod;
use App\Models\RechargeRecord;
use App\Models\Setting;
use App\Models\User;
use App\Models\Distributor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentService
{
    private $gatewayUrl;
    
    public function __construct()
    {
        $this->gatewayUrl = Setting::getValue('payment_gateway_url', 'https://pay.msblog.cc/submit.php');
    }
    
    /**
     * 创建支付订单
     *
     * @param int $planId 套餐ID
     * @param string $paymentMethod 支付方式代码
     * @return array 包含支付URL和订单信息
     */
    public function createPayment($planId, $paymentMethod)
    {
        // 获取套餐信息
        $plans = $this->getAvailablePlans();
        $plan = $plans[$planId] ?? null;
        
        if (!$plan) {
            throw new \Exception('无效的套餐ID');
        }
        
        // 检查支付方式是否可用
        $paymentMethodObj = PaymentMethod::where('code', $paymentMethod)
            ->where('is_enabled', true)
            ->first();
            
        if (!$paymentMethodObj) {
            throw new \Exception('支付方式不可用');
        }
        
        // 获取用户信息
        $user = Auth::user();
        if (!$user) {
            throw new \Exception('用户未登录');
        }
        
        // 获取用户的邀请人（分销商）
        $inviter = $this->getUserInviter($user);
        if (!$inviter) {
            throw new \Exception('无法获取有效的分销商信息');
        }
        
        // 检查分销商的商户ID和密钥是否已设置
        if (!$inviter->merchant_id || !$inviter->merchant_key) {
            throw new \Exception('分销商支付信息未配置');
        }
        
        // 生成唯一订单号
        $orderNo = date('YmdHis') . Str::random(8);
        
        // 创建充值记录
        $rechargeRecord = RechargeRecord::create([
            'user_id' => $user->id,
            'amount' => $plan['price'],
            'days' => $plan['days'],
            'order_no' => $orderNo,
            'payment_method' => $paymentMethod,
            'status' => 'pending'
        ]);
        
        // 准备支付参数
        $params = [
            'pid' => $inviter->merchant_id,
            'type' => $paymentMethod,
            'out_trade_no' => $orderNo,
            'notify_url' => url('/api/payments/notify'),
            'return_url' => url('/api/payments/return'),
            'name' => $plan['name'] . '充值',
            'money' => $plan['price'],
            'param' => $user->id // 传递用户ID作为附加参数
        ];
        
        // 签名
        $params['sign'] = $this->generateSign($params, $inviter->merchant_key);
        $params['sign_type'] = 'MD5';
        
        // 构建支付URL
        $paymentUrl = $this->gatewayUrl . '?' . http_build_query($params);
        
        return [
            'payment_url' => $paymentUrl,
            'order_no' => $orderNo,
            'amount' => $plan['price']
        ];
    }
    
    /**
     * 验证支付回调签名
     *
     * @param array $params 回调参数
     * @return bool 验证结果
     */
    public function verifyCallback($params)
    {
        // 验证必要参数
        if (empty($params['trade_no']) || empty($params['out_trade_no']) || 
            empty($params['trade_status']) || empty($params['sign'])) {
            return false;
        }
        
        // 获取订单记录
        $record = RechargeRecord::where('order_no', $params['out_trade_no'])->first();
        if (!$record) {
            Log::error('支付回调找不到对应订单', ['order_no' => $params['out_trade_no']]);
            return false;
        }
        
        // 获取用户
        $user = User::find($record->user_id);
        if (!$user) {
            Log::error('支付回调找不到对应用户', ['user_id' => $record->user_id]);
            return false;
        }
        
        // 获取用户的邀请人（分销商）
        $inviter = $this->getUserInviter($user);
        if (!$inviter) {
            Log::error('支付回调找不到对应分销商', ['user_id' => $user->id]);
            return false;
        }
        
        // 验证签名
        $sign = $params['sign'];
        $calculatedSign = $this->generateSign($params, $inviter->merchant_key);
        
        if ($sign !== $calculatedSign) {
            Log::error('支付回调签名验证失败', [
                'received' => $sign, 
                'calculated' => $calculatedSign,
                'merchant_id' => $inviter->merchant_id
            ]);
            return false;
        }
        
        // 验证支付状态
        if ($params['trade_status'] !== 'TRADE_SUCCESS') {
            return false;
        }
        
        return true;
    }
    
    /**
     * 处理支付成功回调
     *
     * @param array $params 回调参数
     * @return bool 处理结果
     */
    public function handlePaymentCallback($params)
    {
        // 查找订单
        $record = RechargeRecord::where('order_no', $params['out_trade_no'])
            ->where('status', 'pending')
            ->first();
            
        if (!$record) {
            Log::error('支付回调找不到对应订单', ['order_no' => $params['out_trade_no']]);
            return false;
        }
        
        // 更新订单状态
        $record->status = 'success';
        $record->paid_at = now();
        $record->save();
        
        // 更新用户到期时间
        $user = $record->user;
        $expireTime = $user->expire_time ? new \DateTime($user->expire_time) : new \DateTime();
        
        // 如果已过期，从当前时间开始计算
        if ($expireTime < new \DateTime()) {
            $expireTime = new \DateTime();
        }
        
        // 增加天数
        $expireTime->modify('+' . $record->days . ' days');
        $user->expire_time = $expireTime;
        $user->save();
        
        Log::info('用户充值成功', [
            'user_id' => $user->id,
            'order_no' => $record->order_no,
            'amount' => $record->amount,
            'days' => $record->days,
            'expire_time' => $expireTime->format('Y-m-d H:i:s')
        ]);
        
        return true;
    }
    
    /**
     * 获取用户的邀请人（分销商）
     *
     * @param User $user 用户
     * @return Distributor|null 分销商
     */
    private function getUserInviter(User $user)
    {
        // 首先尝试从用户的邀请关系中获取
        $inviter = $user->inviter()->first();
        if ($inviter) {
            return $inviter;
        }
        
        // 如果用户没有邀请人，获取默认启用的分销商
        return Distributor::where('status', true)->first();
    }
    
    /**
     * 生成MD5签名
     *
     * @param array $params 参数数组
     * @param string $merchantKey 商户密钥
     * @return string 签名
     */
    private function generateSign($params, $merchantKey)
    {
        // 移除sign和sign_type参数
        unset($params['sign']);
        unset($params['sign_type']);
        
        // 移除空值参数
        $params = array_filter($params, function($value) {
            return $value !== '' && $value !== null;
        });
        
        // 按照参数名ASCII码从小到大排序
        ksort($params);
        
        // 拼接成URL键值对格式
        $query = '';
        foreach ($params as $key => $value) {
            $query .= $key . '=' . $value . '&';
        }
        $query = rtrim($query, '&');
        
        // 加上商户密钥并做MD5
        $signStr = $query . $merchantKey;
        return md5($signStr);
    }
    
    /**
     * 获取可用的套餐列表
     *
     * @return array 套餐列表
     */
    public function getAvailablePlans()
    {
        $monthlyPrice = (float) Setting::getValue('monthly_plan_price', 39.9);
        $quarterlyPrice = (float) Setting::getValue('quarterly_plan_price', 99.9);
        $yearlyPrice = (float) Setting::getValue('yearly_plan_price', 299.9);
        
        $plans = [
            1 => [
                'id' => 1,
                'name' => '月卡',
                'price' => $monthlyPrice,
                'description' => '使用期限30天',
                'days' => 30
            ],
            2 => [
                'id' => 2,
                'name' => '季卡',
                'price' => $quarterlyPrice,
                'description' => '使用期限90天，比月卡更优惠',
                'days' => 90
            ],
            3 => [
                'id' => 3,
                'name' => '年卡',
                'price' => $yearlyPrice,
                'description' => '使用期限365天，最佳性价比',
                'days' => 365
            ]
        ];
        
        return $plans;
    }
    
    /**
     * 获取已启用的支付方式
     *
     * @return array 支付方式列表
     */
    public function getEnabledPaymentMethods()
    {
        return PaymentMethod::getEnabledMethods()->toArray();
    }
} 