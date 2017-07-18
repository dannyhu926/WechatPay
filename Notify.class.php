<?php


namespace WeChatPay;

use Think\Log;
use WeChatPay\Model\NotifyReply;
use WeChatPay\Model\Result;

class Notify {

    protected $msg;

    public function getMsg(){
        return $this->msg;
    }

    /**
     * 通知入口
     * @param bool $key 支付密钥
     * @return bool
     */
    final public function handle($key=false){
        $msg = 'OK';
        //获取通知的数据
        $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
        $result = Result::Init($xml,$key);
        if($reply = $this->notifyProcess($result,$msg)){
            //$reply = NotifyReply::create(['return_code'=>'SUCCESS','return_msg'=>'OK']);
            Log::record($reply->toXml(),Log::DEBUG);
            exit($reply->toXml());
        }else{
            $this->msg = $msg;
            return false;
        }
    }

    public function notifyProcess($data, &$msg){
        //TODO 用户基础该类之后需要重写该方法，成功的时候返回true，失败返回false
        return  NotifyReply::create(['return_code'=>'SUCCESS','return_msg'=>'OK']);
    }
}