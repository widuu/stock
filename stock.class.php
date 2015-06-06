<?php

// +----------------------------------------------------------------------
// | 股票类
// +----------------------------------------------------------------------
// | Copyright (c) 2015-2016 http://www.widuu.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: widuu <admin@widuu.com>
// +----------------------------------------------------------------------
// | Time  : 2015/6/6
// +---------------------------------------------------------------------

class stock{

    /**
     * 股票数据接口
     */

    const STOCK_URL = "http://apis.baidu.com/apistore/stockservice/stock";
    
    /**
     * 通过拼音或者汉字获取股票代码
     */

    const SOCKET_SUGGEST = "http://cjhq.baidu.com/suggest?code5=";

    /**
     * 单态实例
     */

    private static $instance;

    /**
     * API 密钥
     */

    private static $apikey;

    /**
     * 实例化类和指定API KEY
     * @param  apikey  string
     * @return instance object
     */

    public static function getInstance($apikey){
        
        if( self::$instance == NULL ){
            self::$instance = new self;
            self::$apikey = $apikey;
        }

        return self::$instance;
    }

    /**
     * 获取股票名称
     * @param  stockid    string <stock num>
     * @return stockName  string
     */

    public static function getName($stockid){
        $result = self::getSingleStock($stockid);
        return $result['name'];
    }

    /**
     * 获取最后更新时间
     * @param  stockid string <stock num>
     * @return time    string
     */

    public static function getTime($stockid){
        $result = self::getSingleStock($stockid);
        return $result['date'].$result['time'];
    }

    /**
     * 获取K线图地址
     * @param  stockid  string <stock num>
     * @param  date     string <time> min/day/week/mouth
     * @return imageUrl string
     */

    public static function getKline($stockid,$date='min'){
        $result = self::getSingleStock($stockid);
        return $result['klinegraph'][$date.'url'];
    }

    /**
     * 抓取整只股票的数据
     * @param  stockid  string <stock num>
     * @return stock infomation array
     */

    public static function getSingleStock($stockid){
        $type = preg_match('/(\d+){6}/is', $stockid);
        if ( $type == 0 ){
            $stockid = self::getStockId($stockid);
        }
        $stock_url = self::STOCK_URL."?stockid=".$stockid;
        $result = self::httpGet( $stock_url , true );
        if( $result['errNum'] != 0 ){
            throw new Exception($result['errMsg'], 1);
            return;
        }
        return $result['retData'];
    }

    /**
     * 输入拼音或者汉字来获取股票代码
     * @param  name    string <stock name>
     * @return stockid string
     */

    private static function getStockId($name){
        $result = self::httpGet( self::SOCKET_SUGGEST.urlencode(iconv('utf-8', 'GBK', $name)),false );
        if (empty($result)){
            throw new Exception("stock name not exists", 2);
            return;
        }
        $stockid = $result['Result'][0]['code'];
        $stock   = explode('.', $stockid);
        return   $stock[1].$stock[0];
    }

    /**
     * GET获取方法
     * @param  param string  参数 
     * @author widuu <admin@widuu.com>
     */

    private static function httpGet($url,$header=false) { 
        $curlHandle = curl_init(); 
        curl_setopt( $curlHandle , CURLOPT_URL, $url );
        if( $header ){
           curl_setopt( $curlHandle , CURLOPT_HTTPHEADER  , array('apikey:'.self::$apikey)); 
        } 
        curl_setopt( $curlHandle , CURLOPT_RETURNTRANSFER, 1 ); 
        curl_setopt( $curlHandle , CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt( $curlHandle , CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt( $curlHandle , CURLOPT_TIMEOUT, 10 ); 
        $content = curl_exec( $curlHandle );
        curl_close( $curlHandle ); 
        return $header ? json_decode($content,true) :json_decode(iconv('GBK','utf-8',trim($content)),true); 
    }
}

//测试代码
stock::getInstance("5040bcbfebb0a4cffc7be278723255aa");
echo "<pre>";
print_r(stock::getSingleStock('sh601000'));
echo "</pre>";
echo stock::getKline('紫金矿业');