<?php
namespace libs\verificationCode;
//$vc = new VerificationCode();
//$vc->buildCode();
////echo "问题:".$vc->getCodeProblem()."答案:".$vc->getCodeResult();
//
//
//$vc->showCodeGIF();



class VerificationCode{
    private $charset = 'abcdefghkmnprstuvwxyzABCDEFGHKMNPRSTUVWXYZ23456789';//随机因子
    private $width;//画布宽度
    private $height ;//画布高度
    private $img;//图形资源句柄

    private $font = array('/font/segoeuib.ttf','/font/Algerian.ttf');//指定的字体
    private $font_max = 1;

    //验证码问题组合 : {$num1}{$operator}{$num2}
    private $num1;
    private $num2;
    private $operator;
    //验证码答案
    private $code;//验证码答案



    /**
     * 获取验证问题
     */
    public function getCodeProblem(){
        return $this->num1.$this->operator.$this->num2;
    }
    /**
     * 获取验证答案
     */
    public function getCodeResult(){
        return $this->code;
    }
    /**
     * 获取验证码图像
     */
    public function showCodeGIF(){
        $this->readyCanvas();//构造指定大小的空白画布
        $this->setbackgroundcolor();//设置画布颜色
        $this->createSnow();//雪花
        $this->set_code();//写验证码
        $this->createLine();//干扰线
        $this->setdistrubecode();//画干扰字符
        Header("Content-type: image/GIF");
        ImageGIF($this->img);//构造gif
        ImageDestroy($this->img);
    }

    /**
     * 生成验证码
     */
    public function buildCode(){
        $algorithm = rand(0,2);
        if($algorithm == 0){
            $num1 = rand(1,80);
            $num2 = rand(1,9);
            $code = $num1+$num2;
            $operator = '加';
        }else if ($algorithm == 1){
            $num1 = rand(10,99);
            $num2 = rand(1,9);
            $code = $num1-$num2;
            $operator = '减';
        }else if ($algorithm == 2){;
            $num1 = rand(1,9);
            $num2 = rand(2,9);
            $code = $num1*$num2;
            $operator = '乘';
        }
//        }else if ($algorithm == 3){
//            do{
//                $num1 = rand(100,900);
//                $num2 = rand(20,30);
//                $code = $num1/$num2;
//            }while($code % 2 != 0);//整除
//            $operator = '=';
//        }


        $this->num1 = $num1.'';
        $this->num2 = $num2.'';
        $this->operator = $operator;
        $this->code = $code;
    }
//    /**
//     *生成验证码内容
//     */
//    private function buildCode(){
//        $originalcode = $this->charset;
//        $countdistrub = strlen($originalcode);
//        $_dscode = "";
//        $counts=$this->codelen;//验证码位数
//        for($j=0;$j<$counts;$j++){
//            $dscode = $originalcode[rand(0,$countdistrub-1)];
//            $_dscode.=$dscode;
//        }
//        return $_dscode;
//
//    }
//

    /**
     *准备空白画布
     */
    private function readyCanvas(){
        $this->height = 40;
        $this->width= 40 * (strlen($this->getCodeProblem())-2);
        $this->img = imagecreate($this->width,$this->height);
    }
//
    /**
     *设置画布颜色
     */
    private function setbackgroundcolor(){
        $bgcolor = ImageColorAllocate($this->img, rand(200,255),rand(200,255),rand(200,255));
        imagefill($this->img,0,0,$bgcolor);
    }

    /**
     *生成线条
     */
    private function createLine() {
        //线条
        for ($i=0;$i<6;$i++) {
            $color = imagecolorallocate($this->img,mt_rand(0,156),mt_rand(0,156),mt_rand(0,156));
            imageline($this->img,mt_rand(0,$this->width),mt_rand(0,$this->height),mt_rand(0,$this->width),mt_rand(0,$this->height),$color);
        }

    }
    /**
     *生成雪花
     */
    private function createSnow() {
        //雪花
        for ($i=0;$i<100;$i++) {
            $color = imagecolorallocate($this->img,mt_rand(200,255),mt_rand(200,255),mt_rand(200,255));
            imagestring($this->img,mt_rand(1,5),mt_rand(0,$this->width),mt_rand(0,$this->height),'*',$color);
        }
    }
    /**
     *画验证码
     */
    private function set_code(){
        //num1
        $base=floor($this->width/(strlen($this->getCodeProblem())-2))+3;


        $i=0;
        $y=floor($this->height/2)+floor($this->height/3);
        for($len=0,$counts=strlen($this->num1);$len<$counts;$len++){
            imagettftext($this->img,rand(24,37),rand(-30,45),$i*$base+10,$y,ImageColorAllocate($this->img,rand(0,50),rand(50,100),rand(100,140)),__DIR__ . '/font/segoeuib.ttf',$this->num1[$len]);
            $i++;
        }



        imagettftext($this->img,rand(23,27),rand(-10,10),$i*$base,$y,ImageColorAllocate($this->img,rand(0,50),rand(50,100),rand(100,140)),__DIR__ .'/font/LiheiPro.ttf',$this->to_entities($this->operator));
        $i++;


        for($len=0,$counts=strlen($this->num2);$len<$counts;$len++){
            imagettftext($this->img,rand(25,37),rand(-45,20),$i*$base-10,$y,ImageColorAllocate($this->img,rand(0,50),rand(50,100),rand(100,140)),__DIR__ .$this->font[rand(0,$this->font_max)],$this->num2[$len]);
            $i++;
        }

    }
    /**
     *画干扰字符
     */
    private function setdistrubecode(){
        for($i=0;$i<10;$i++){
            imagettftext($this->img,rand(8,15),rand(0,360),rand(0,$this->width),rand(0,$this->height),ImageColorAllocate($this->img, rand(40,140),rand(40,140),rand(40,140)),__DIR__ .$this->font[0],$this->charset[rand(0,strlen($this->charset)-1)]);

        }
    }
    function to_entities($string){
        $len = strlen($string);
        $buf = "";
        for($i = 0; $i < $len; $i++){
            if (ord($string[$i]) <= 127){
                $buf .= $string[$i];
            } else if (ord ($string[$i]) <192){
                //unexpected 2nd, 3rd or 4th byte
                $buf .= "&#xfffd";
            } else if (ord ($string[$i]) <224){
                //first byte of 2-byte seq
                $buf .= sprintf("&#%d;",
                    ((ord($string[$i + 0]) & 31) << 6) +
                    (ord($string[$i + 1]) & 63)
                );
                $i += 1;
            } else if (ord ($string[$i]) <240){
                //first byte of 3-byte seq
                $buf .= sprintf("&#%d;",
                    ((ord($string[$i + 0]) & 15) << 12) +
                    ((ord($string[$i + 1]) & 63) << 6) +
                    (ord($string[$i + 2]) & 63)
                );
                $i += 2;
            } else {
                //first byte of 4-byte seq
                $buf .= sprintf("&#%d;",
                    ((ord($string[$i + 0]) & 7) << 18) +
                    ((ord($string[$i + 1]) & 63) << 12) +
                    ((ord($string[$i + 2]) & 63) << 6) +
                    (ord($string[$i + 3]) & 63)
                );
                $i += 3;
            }
        }
        return $buf;
    }

}