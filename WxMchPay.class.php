<?php
// 引入SDK
import('Common.Util.WxPay');

/**
 * 微信企业付款操作类
 * Author  :  Max.wen
 * DateTime: <15/9/16 11:00>
 */
class WxMchPay extends Wxpay_client_pub
{
    /**
     * API 参数
     * @var array
     * 'mch_appid'         # 公众号APPID
     * 'mchid'             # 商户号
     * 'device_info'       # 设备号
     * 'nonce_str'         # 随机字符串
     * 'partner_trade_no'  # 商户订单号
     * 'openid'            # 收款用户openid
     * 'check_name'        # 校验用户姓名选项 针对实名认证的用户
     * 're_user_name'      # 收款用户姓名
     * 'amount'            # 付款金额
     * 'desc'              # 企业付款描述信息
     * 'spbill_create_ip'  # Ip地址
     * 'sign'              # 签名
     */
    public $parameters = [];

    public function __construct() {
        $this->url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';
        $this->curl_timeout = WxPayConf_pub::CURL_TIMEOUT;
    }

    /**
     * 生成请求xml数据
     * @return string
     */
    public function createXml() {
        $this->parameters['mch_appid'] = WxPayConf_pub::APPID;
        $this->parameters['mchid'] = WxPayConf_pub::MCHID;
        $this->parameters['nonce_str'] = $this->createNoncestr();
        $this->parameters['sign'] = $this->getSign($this->parameters);
        return $this->arrayToXml($this->parameters);
    }


    /**
     *     作用：使用证书，以post方式提交xml到对应的接口url
     */
    function postXmlSSLCurl($xml, $url, $second = 30) {
        $ch = curl_init();
        //超时时间
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        //这里设置代理，如果有的话
        //curl_setopt($ch,CURLOPT_PROXY, '8.8.8.8');
        //curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //设置证书
        curl_setopt($ch, CURLOPT_CAINFO, WxPayConf_pub::SSLROOTCA_PATH);
        //使用证书：cert 与 key 分别属于两个.pem文件
        //默认格式为PEM，可以注释
        curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
        curl_setopt($ch, CURLOPT_SSLCERT, WxPayConf_pub::SSLCERT_PATH);
        //默认格式为PEM，可以注释
        curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');
        curl_setopt($ch, CURLOPT_SSLKEY, WxPayConf_pub::SSLKEY_PATH);

        //post提交方式
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        $data = curl_exec($ch);
        //返回结果
        if ($data) {
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            echo "curl出错，错误码:$error" . "<br>";
            echo "<a href='http://curl.haxx.se/libcurl/c/libcurl-errors.html'>错误原因查询</a></br>";
            curl_close($ch);
            return false;
        }
    }

    /**
     * 企业支付
     * @param string $openid   用户openID
     * @param string $trade_no 单号
     * @param string $moneyFen 金额
     * @param string $desc     描述
     * @return string XML      结构的字符串
     */
    public function toPay($openid, $trade_no, $moneyFen, $desc) {
        // 用户openid
        $this->setParameter('openid', $openid);
        // 商户订单号
        $this->setParameter('partner_trade_no', $trade_no);
        // 校验用户姓名选项
        $this->setParameter('check_name', 'NO_CHECK');
        // 企业付款金额  单位为分
        $this->setParameter('amount', $moneyFen);
        // 企业付款描述信息
        $this->setParameter('desc', $desc);
        // 调用接口的机器IP地址  自定义
        $this->setParameter('spbill_create_ip', '127.0.0.1'); # getClientIp()
        // 收款用户姓名
        // $this->setParameter('re_user_name', 'Max wen');
        // 设备信息
        // $this->setParameter('device_info', 'dev_server');

        $response = $this->postXmlSSL();
        if (!empty($response)) {
            $data = simplexml_load_string($response, null, LIBXML_NOCDATA);
            if ($data->return_code == 'SUCCESS') {
                $result = array(
                    'return_code' => $data->result_code,
                    'return_msg'  => "错误代码：{$data->err_code},错误描叙：" . $data->err_code_des,
                );
            } elseif ($data->return_code == 'FAIL') {
                $result = array(
                    'return_code' => $data->return_code,
                    'return_msg'  => $data->return_msg
                );
            }
        } else {
            $result = array('return_code' => 'FAIL', 'return_msg' => 'transfers_接口出错');
        }
        return $result;
    }
}