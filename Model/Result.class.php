<?php

namespace WeChatPay\Model;


class Result  extends Base{

    public function checkSign(){
        if(!$this->has('sign')){
            throw new \Exception('签名错误!');
        }
        $sign = $this->makeSign($this->getKey());
        if($sign != $this->sign){
            throw new \Exception('签名错误!');
        }
        return true;
    }

    public static function init($xml,$key=false){
        $obj = self::create($xml,'xml');
        if($obj->has('return_code') && $obj->return_code != 'SUCCESS'){
            return $obj->toArray();
        }
        if($key){
            $obj->setKey($key);
            $obj->checkSign();
        }
        return $obj->toArray();
    }

}