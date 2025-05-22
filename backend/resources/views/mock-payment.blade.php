<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>模拟支付页面</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .payment-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
            width: 100%;
            max-width: 500px;
            text-align: center;
        }
        .payment-header {
            margin-bottom: 30px;
        }
        .payment-header h1 {
            color: #409EFF;
            margin-bottom: 10px;
        }
        .payment-details {
            margin-bottom: 30px;
            text-align: left;
            padding: 0 20px;
        }
        .payment-details p {
            margin: 10px 0;
            display: flex;
            justify-content: space-between;
        }
        .payment-details strong {
            color: #333;
        }
        .payment-amount {
            font-size: 24px;
            color: #f56c6c;
            font-weight: bold;
            margin: 20px 0;
        }
        .payment-actions {
            margin-top: 30px;
        }
        .payment-actions button {
            background-color: #409EFF;
            color: white;
            border: none;
            padding: 12px 30px;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .payment-actions button:hover {
            background-color: #337ecc;
        }
        .payment-qrcode {
            margin: 20px auto;
            width: 200px;
            height: 200px;
            background-color: #eee;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            color: #888;
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <div class="payment-header">
            <h1>软件管理系统支付</h1>
            <p>请使用{{ request('payment_method') == 'wechat' ? '微信' : '支付宝' }}扫码完成支付</p>
        </div>
        
        <div class="payment-details">
            <p>
                <span>订单号：</span>
                <strong>{{ request('order_id', 'ORD' . time()) }}</strong>
            </p>
            <p>
                <span>交易类型：</span>
                <strong>软件充值</strong>
            </p>
        </div>
        
        <div class="payment-amount">
            ¥ {{ number_format(request('amount', 0), 2) }}
        </div>
        
        <div class="payment-qrcode">
            模拟二维码<br>
            实际项目中这里会展示支付二维码
        </div>
        
        <div class="payment-actions">
            <button onclick="simulatePayment()">模拟支付完成</button>
        </div>
    </div>
    
    <script>
        function simulatePayment() {
            alert('支付成功！');
            window.opener && window.opener.postMessage('payment_success', '*');
            setTimeout(() => {
                window.close();
            }, 1000);
        }
    </script>
</body>
</html> 