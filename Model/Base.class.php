<?php


namespace WeChatPay\Model;


class Base {

    protected $values = [];

    protected $allowed = [];

    protected $key;

    /**
     * 创建一个数据对象,支持数组和XML两种格式
     * base::create($arr);
     * @param $data
     * @param string $type
     * @return Base
     * @throws \Exception
     */
    public static function create($data,$type='array'){
        if('xml' == $type){
            $data = self::xmlToArray($data);
        }
        $obj = new self();
        $obj->fill($data);
        return $obj;
    }

    /**
     * 批量赋值
     * @param $data
     */
    public function fill($data){
        if(!empty($this->allowed)){
            foreach($data as $key=>$val){
                if(in_array($key,$this->allowed)){
                    $this->values[$key] = $val;
                }
            }
        }else{
            $this->values = $data;
        }
    }

    public function __set($key,$value){
        $this->values[$key] = $value;
        return $value;
    }

    public function __get($key){
        if(isset($this->values[$key])){
            return $this->values[$key];
        }
        return null;
    }

    /**
     * 判断KEY是否存在
     * @param $key
     * @return bool
     */
    public function has($key){
        return isset($this->values[$key]);
    }

    /**
     * 将数据转化为url参数
     * @return string
     */
    public function toUrlParams($except=[]){
        $buff = "";
        foreach ($this->values as $k => $v){
            if(!in_array($k,$except) && $v != "" && !is_array($v)){
                $buff .= $k . "=" . $v . "&";
            }
        }
        $buff = trim($buff, "&");
        return $buff;
    }

    /**
     * 转化为数组
     * @return mixed
     */
    public function toArray(){
        return $this->values;
    }

    /**
     * 将数据转化为XML
     * @return string
     */
    public function toXml(){
        $xml = "<xml>";
        foreach ($this->values as $key=>$val)
        {
            if (is_numeric($val)){
                $xml.="<".$key.">".$val."</".$key.">";
            }else{
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
            }
        }
        $xml.="</xml>";
        return $xml;
    }

    /**
     * XML转数组
     * @param $xml
     * @return mixed
     * @throws \Exception
     */
    public static function xmlToArray($xml){
        if(!$xml){
            throw new \Exception("xml数据异常！");
        }
        //将XML转为array
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        return json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    }

    public function setKey($key){
        $this->key = $key;
    }

    public function getKey(){
        return $this->key;
    }

    /**
     * 设置签名
     * @param $key
     * @return string
     */
    public function setSign($key){
        $this->setKey($key);
        $sign = $this->makeSign($key);
        $this->values['sign'] = $sign;
        return $sign;
    }

    /**
     * 生成签名
     * @param $key
     * @return string
     */
    public function makeSign($key){
        //签名步骤一：按字典序排序参数
        ksort($this->values);
        $string = $this->toUrlParams(['sign']);
        //签名步骤二：在string后加入KEY
        $string = $string . "&key={$key}";
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        return $result;
    }

    /**
     * 设置32未随机字符串
     */
    public function setNonceStr(){
        $this->values['nonce_str'] = self::getNonceStr();
    }

    /**
     * 生成32位随机字符串
     * @param int $length
     * @return string
     */
    public static function getNonceStr($length = 32){
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str ="";
        for ( $i = 0; $i < $length; $i++ )  {
            $str .= substr($chars, mt_rand(0, strlen($chars)-1), 1);
        }
        return $str;
    }
}