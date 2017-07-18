<?php
namespace WeChatPay;


use Exception;
use WeChatPay\Model\Base;
use WeChatPay\Model\Result;

class Pay {

    /**
     * 统一下单接口
     * @param $inputObj
     * @return mixed
     * @throws \Exception
     * @throws \Exceptionx
     */
    public static function unifiedOrder($inputObj){
        //检测必填参数
        if(!$inputObj->has('out_trade_no')) {
            throw new \Exception("缺少统一支付接口必填参数out_trade_no！");
        }else if(!$inputObj->has("body")){
            throw new \Exception("缺少统一支付接口必填参数body！");
        }else if(!$inputObj->has('total_fee')) {
            throw new \Exception("缺少统一支付接口必填参数total_fee！");
        }else if(!$inputObj->has('trade_type')) {
            throw new \Exceptionx("缺少统一支付接口必填参数trade_type！");
        }
        //关联参数
        if($inputObj->trade_type == "JSAPI" && !$inputObj->has('openid')){
            throw new \Exceptionx("统一支付接口中，缺少必填参数openid！trade_type为JSAPI时，openid为必填参数！");
        }
        if($inputObj->trade_type == "NATIVE" && !$inputObj->has('product_id')){
            throw new \Exceptionx("统一支付接口中，缺少必填参数product_id！trade_type为JSAPI时，product_id为必填参数！");
        }
        $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
        return self::execute($url,$inputObj,new XmlCurl());
    }

    /**
     * 订单查询接口
     * @param $inputObj
     * @return mixed
     * @throws \Exception
     */
    public static function orderQuery($inputObj){
        $url = "https://api.mch.weixin.qq.com/pay/orderquery";
        //检测必填参数
        if(!$inputObj->has('out_trade_no') && !$inputObj->has('transaction_id')) {
            throw new \Exception("订单查询接口中，out_trade_no、transaction_id至少填一个！");
        }
        return self::execute($url,$inputObj,new XmlCurl());
    }

    /**
     * 关闭订单接口
     * @param $inputObj
     * @return mixed
     */
    public static function closeOrder($inputObj){
        $url = "https://api.mch.weixin.qq.com/pay/closeorder";
        //检测必填参数
        if(!$inputObj->has('out_trade_no')) {
            throw new \Exception("订单查询接口中，out_trade_no必填！");
        }
        return self::execute($url,$inputObj,new XmlCurl());
    }

    /**
     * 订单退款接口
     * @param $inputObj
     * @return mixed
     * @throws \Exception
     */
    public static function refund($inputObj,$cert){
        $url = "https://api.mch.weixin.qq.com/secapi/pay/refund";
        //检测必填参数
        if(!$inputObj->has('out_trade_no') && !$inputObj->has('transaction_id')) {
            throw new \Exception("退款申请接口中，out_trade_no、transaction_id至少填一个！");
        }else if(!$inputObj->has('out_refund_no')){
            throw new \Exception("退款申请接口中，缺少必填参数out_refund_no！");
        }else if(!$inputObj->has('total_fee')){
            throw new \Exception("退款申请接口中，缺少必填参数total_fee！");
        }else if(!$inputObj->has('refund_fee')){
            throw new \Exception("退款申请接口中，缺少必填参数refund_fee！");
        }else if(!$inputObj->has('op_user_id')){
            throw new \Exception("退款申请接口中，缺少必填参数op_user_id！");
        }
        return self::execute($url,$inputObj,new XmlCurl($cert));
    }

    /**
     * 查询退款接口
     * @param $inputObj
     * @return mixed
     * @throws Exception
     */
    public static function refundQuery($inputObj){
        $url = "https://api.mch.weixin.qq.com/pay/refundquery";
        //检测必填参数
        if(!$inputObj->has('out_refund_no') &&
            !$inputObj->has('out_trade_no') &&
            !$inputObj->has('transaction_id') &&
            !$inputObj->has('refund_id')) {
            throw new \Exception("退款查询接口中，out_refund_no、out_trade_no、transaction_id、refund_id四个参数必填一个！");
        }
        return self::execute($url,$inputObj,new XmlCurl());
    }

    /**
     * 下载对账单接口
     * @param Base $inputObj
     * @return mixed|string
     * @throws \Exception
     */
    public static function downloadBill(Base $inputObj){
        $url = "https://api.mch.weixin.qq.com/pay/downloadbill";
        //检测必填参数
        if(!$inputObj->has('bill_date')) {
            throw new \Exception("对账单接口中，缺少必填参数bill_date！");
        }
        $xmlCurl = new XmlCurl();
        $response = $xmlCurl->post($inputObj->toXml(),$url);
        if(substr($response, 0 , 5) == "<xml>"){
            return "";
        }
        return $response;
    }

    /**
     * 提交被扫支付API
     * 收银员使用扫码设备读取微信用户刷卡授权码以后，二维码或条码信息传送至商户收银台，
     * 由商户收银台或者商户后台调用该接口发起支付。
     * WxPayWxPayMicroPay中body、out_trade_no、total_fee、auth_code参数必填
     * appid、mchid、spbill_create_ip、nonce_str不需要填入
     */
    public static function micropay(Base $inputObj){
        $url = "https://api.mch.weixin.qq.com/pay/micropay";
        //检测必填参数
        if(!$inputObj->has('body')) {
            throw new \Exception("提交被扫支付API接口中，缺少必填参数body！");
        } else if(!$inputObj->has('out_trade_no')) {
            throw new \Exception("提交被扫支付API接口中，缺少必填参数out_trade_no！");
        } else if(!$inputObj->has('total_fee')) {
            throw new \Exception("提交被扫支付API接口中，缺少必填参数total_fee！");
        } else if(!$inputObj->has('auth_code')) {
            throw new \Exception("提交被扫支付API接口中，缺少必填参数auth_code！");
        }
        return self::execute($url,$inputObj,new XmlCurl());
    }

    /**
     *
     * 撤销订单API接口，out_trade_no和transaction_id必须填写一个
     * appid、mchid、spbill_create_ip、nonce_str不需要填入
     * @param  $inputObj
     * @throws Exception
     */
    public static function reverse(Base $inputObj,$cert){
        $url = "https://api.mch.weixin.qq.com/secapi/pay/reverse";
        //检测必填参数
        if(!$inputObj->has('out_trade_no') && !$inputObj->has('transaction_id')) {
            throw new \Exception("撤销订单API接口中，参数out_trade_no和transaction_id必须填写一个！");
        }
        return self::execute($url,$inputObj,new XmlCurl($cert));
    }

    /**
     *
     * 转换短链接
     * 该接口主要用于扫码原生支付模式一中的二维码链接转成短链接(weixin://wxpay/s/XXXXXX)，
     * 减小二维码数据量，提升扫描速度和精确度。
     * appid、mchid、spbill_create_ip、nonce_str不需要填入
     * @param Base $inputObj
     * @param int $timeOut
     * @throws Exception
     * @return 成功时返回，其他抛异常
     */
    public static function shorturl(Base $inputObj){
        $url = "https://api.mch.weixin.qq.com/tools/shorturl";
        //检测必填参数
        if(!$inputObj->has('long_url')) {
            throw new \Exception("需要转换的URL，签名用原串，传输需URL encode！");
        }
        return self::execute($url,$inputObj,new XmlCurl());
    }

    /**
     * 企业付款
     * @param $inputObj
     * @return mixed
     */
    public static function transfers($inputObj){
        $url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';

        return self::execute($url,$inputObj,new XmlCurl());
    }

    /**
     * 获取企业付款信息
     * @param $inputObj
     * @return mixed
     */
    public static function getTransferInfo($inputObj){
        $url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/gettransferinfo';
        return self::execute($url,$inputObj,new XmlCurl());
    }

    private static function execute($url,$inputObj,$xmlCurl){
        $xml = $inputObj->toXml();
        $response = $xmlCurl->post($xml,$url);
        $result = Result::init($response);
        return $result;
    }
}