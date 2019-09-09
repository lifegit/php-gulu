<?php
/**
 *author:TheLife
 *version:1.0
 *time:2018-08-10
 *更多见:http://www.php.net/manual/zh/function.curl-setopt.php
 */

namespace libs;

class Curl {

//超时
    private function init($url,$requestHeaders,$isResponseHeaders,$isResponseBodys,$timeOut,$isLocation){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);// 	当 HTTP 状态码大于等于 400，TRUE 将将显示错误详情。 默认情况下将返回页面，忽略 HTTP 代码。
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);// 	TRUE 将curl_exec()获取的信息以字符串返回，而不是直接echo输出。
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//FALSE 禁止 cURL 验证对等证书（peer's certificate）。要验证的交换证书可以在 CURLOPT_CAINFO 选项中设置，或在 CURLOPT_CAPATH中设置证书目录。
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $isLocation);// 	TRUE 时将会根据服务器返回 HTTP 头中的 "Location: " 重定向。（注意：这是递归的，"Location: " 发送几次就重定向几次，除非设置了 CURLOPT_MAXREDIRS，限制最大重定向次数。）。
        curl_setopt($ch, CURLOPT_HTTPHEADER,$requestHeaders);// 	设置 HTTP 头字段的数组。格式例： array('Content-type: text/plain', 'User-Agent: Mozilla/5.0 (Windows NT 6.1; rv:61.0) Gecko/20100101 Firefox/61.0')
        curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS,$timeOut);      //  3秒超时
        if($isResponseHeaders)
            curl_setopt($ch, CURLOPT_HEADER, true);//返回response头部信息
        if(! $isResponseBodys)
            curl_setopt($ch, CURLOPT_NOBODY, true); //  	TRUE 时将不输出 BODY 部分。同时 Mehtod 变成了 HEAD。修改为 FALSE 时不会变成 GET。

        return $ch;
    }
    private function exec($ch){
        //curl_setopt($ch, CURLINFO_HEADER_OUT, true); //TRUE 时追踪句柄的请求字符串，从 PHP 5.1.3 开始可用。这个很关键，就是允许你查看请求header //debug
        $reponse = curl_exec($ch);
        if($reponse === false)
            return ['exec'=>false];
        //echo curl_getinfo($ch, CURLINFO_HEADER_OUT); //官方文档描述是“发送请求的字符串”，其实就是请求的header。这个就是直接查看请求header，因为上面 CURLINFO_HEADER_OUT 允许查看 //debug
        $code = curl_getinfo($ch,CURLINFO_HTTP_CODE);
        if( substr($reponse,0,7) === 'HTTP/1.' ){
            $header = [];
            $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $headerStr = substr($reponse, 0,$headerSize );
            $arr = explode("\n",$headerStr);
            foreach($arr as $value){
                if(($p = strpos($value,':'))  !== false){
                    $key = substr($value,0,$p);
                    $value = trim(substr($value,$p+1,strlen($value)));
                    $header[$key] = $value;
                }
            }
            $body = substr($reponse, $headerSize,strlen($reponse));
        }else{
            $header = [];
            $body = $reponse;
        }
        curl_close($ch); //用完记得关掉他
        //var_dump($reponse);
        return ['exec'=>true,'responseCode'=>$code,'responseBodys'=>$body,'responseHeaders'=>$header];
    }

    /**
     * @param $url
     * @param $requestHeaders array|true 提交的header
     * @param bool $isResponseHeaders 是否返回headers,如果不需要可节省流量
     * @param bool $isResponseBodys 是否返回bodys,如果不需要可节省流量
     * @param bool $isLocation  是否强制跳转
     * @return array
     */
    public function  get($url,$requestHeaders = [],$isResponseHeaders = false,$isResponseBodys = true,$isLocation = true){
        $ch = $this->init($url,$requestHeaders,$isResponseHeaders,$isResponseBodys,3500,$isLocation);
        return $this->exec($ch);
    }

    /**
     * @param $url
     * @param mixed $postData 提交的post数据
     * @param $requestHeaders array|true 提交的header
     * @param bool $isResponseHeaders 是否返回headers,如果不需要可节省流量
     * @param bool $isResponseBodys 是否返回bodys,如果不需要可节省流量
     * @param bool $isLocation  是否强制跳转
     * @return array
     */
    public function  post($url,$postData = '',$requestHeaders =[],$isResponseHeaders = false,$isResponseBodys = true,$isLocation = true){
        $ch = $this->init($url,$requestHeaders,$isResponseHeaders,$isResponseBodys,3500,$isLocation);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        return $this->exec($ch);
    }



}
