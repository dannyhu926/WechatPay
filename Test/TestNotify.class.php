<?php
use Think\Log;
use WeChatPay\Model\Base;
use WeChatPay\Model\NotifyReply;
use WeChatPay\Notify;
use WeChatPay\Pay;

class TestNotify extends Notify {

    public function notifyProcess($data,&$msg){
        Log::record('回调返回数据:'.json_encode($data),Log::DEBUG);
        $openid = $data['openid'];
        //$sub_openid = $data['sub_openid'];
        $product_id = $data['product_id'];

        $unifiedOrder = Base::create([
            'appid' => C('MASTER.APP_ID'),    //公众号
            'mch_id' => C('MASTER.MCH_ID2'),   //商户号
            //'sub_appid' => C('SUB_TEST1.APP_ID'),//子商户公众账号ID,微信分配的子商户公众账号ID，如需在支付完成后获取sub_openid则此参数必传
            //'sub_mch_id' => C('SUB_TEST1.MCH_ID'),
            'is_subscribe' =>'Y',
            //'device_info' => '001',                    //设备号
            'body' => '扫码支付模式一测试',
            //'detail' => '商品详情',
            //'attach' => '附加信息回掉时会返回',
            'out_trade_no' => C('MASTER.MCH_ID2').date('YmdHis'), //商户订单号
            //'fee_type' => 'CNY', //货币类型,默认CNY(人民币)
            'total_fee' => $product_id,   //支付总金额，单位：分
            'spbill_create_ip' => get_client_ip(), //终端IP
            'time_start' =>date("YmdHis"), //开始时间
            'time_expire' => date("YmdHis", time() + 600), //失效时间，不得小于5分钟
            'goods_tag' => 'test', //商品标识
            'notify_url' => 'http://'.$_SERVER['HTTP_HOST'].U('/Pay/Notify/test'),
            'trade_type' => 'NATIVE', //交易类型,取值如下：JSAPI，NATIVE，APP，WAP
            'product_id' => $product_id, //trade_type=NATIVE，此参数必传。此id为二维码中包含的商品ID，商户自行定义。
            //'limit_pay' => 'no_credit',//指定支付方式,no_credit--指定不能使用信用卡支付
            'openid' => $openid,
            //'sub_openid' => $openid
        ]);
        $unifiedOrder->setNonceStr();
        $unifiedOrder->setSign(C('MASTER.KEY'));
        $result = Pay::unifiedOrder($unifiedOrder);
        Log::record('统一下单结果:'.json_encode($result),Log::DEBUG);
        if(!array_key_exists("appid", $result) ||
            !array_key_exists("mch_id", $result) ||
            !array_key_exists("prepay_id", $result))
        {
            $msg = "统一下单失败";
            return false;
        }
        $reply = NotifyReply::create([
            'return_code'=>'SUCCESS',
            'return_msg'=>'OK',
            'appid'=>$result["appid"],
            'mch_id'=>$result["mch_id"],
            'prepay_id'=>$result["prepay_id"],
            'nonce_str'=>NotifyReply::getNonceStr(),
            'err_code_des'=>'OK',
            'result_code'=>'SUCCESS'
        ]);
        $reply->setSign(C('MASTER.KEY'));
        return $reply;
    }

}