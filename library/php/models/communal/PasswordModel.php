<?php
namespace models\communal;

//[1] > $pw = 123456
//// 123456
//[2] > $hashed = PasswordModel->make($pw);
//// '$2y$10$xSugoyKv765TY8DsERJ2/.mPIOwLNdM5Iw1n3x1XNVymBlHNG4cX6'
//[3] > PasswordModel->check($hashed, $pw);
//// false
//[4] > PasswordModel->check($pw, $hashed);
//// true
///
class PasswordModel {

    const HASH = 'hash';
    const KEY_SALT = 'salt';
    const KEY_CODE = 'code';

    // see: \Illuminate\Hashing\BcryptHasher
    public function make($value)
    {
        $hash = password_hash($value, PASSWORD_BCRYPT, [
            'cost' => 10,
        ]);

        if ($hash === false) {
            return \Create::error('Bcrypt hashing not supported.');
        }
        return \Create::success([self::HASH => $hash]);
    }

    /**
     * Check the given plain value against a hash.
     *
     * @param  string  $value
     * @param  string  $hashedValue
     * @param  array   $options
     * @return bool
     */
    public function check($value, $hashedValue)
    {
        if (strlen($hashedValue) === 0) {
            return false;
        }

        return password_verify($value, $hashedValue);
    }


    /**
     * 生产salt和code
     * @return array
     */
    public function randSaltAndCode(){
        $rand = new \libs\Rand();
        $salt = $rand->getRandChar(\libs\Rand::TYPE_ALL,16);
        $code = $rand->getRandChar(\libs\Rand::TYPE_NUM,mt_rand(6,12));
        return [self::KEY_SALT => $salt,self::KEY_CODE => $code];
    }

    /**
     * salt加密。服务端私钥加密，客户端公钥解密
     * @param $salt
     * @param $privateKey
     * @return string
     */
    public function saltEncrypt($salt,$privateKey){
        $pi_key =  openssl_pkey_get_private($privateKey);
        openssl_private_encrypt($salt, $encrypted, $pi_key, OPENSSL_PKCS1_PADDING);
        return base64_encode($encrypted);
    }

    /**
     * 解密密码
     * 原理:
        //     AES-128-CBC {padding:Pkcs7,iv:salt}(
        //         md5( md5(salt + 验证码)+sha512(单数位 salt) ).从第三个开始取16个长度
        //     AES-128-CBC {padding:Pkcs7,iv:md5(salt)的前16位}(
        //         sha512(sha384(salt) + 验证码长度 + 验证码 + md5(双数位 salt)).从第十个开始取16个长度,
        //         sha512(sha384(salt) + 验证码长度 + 验证码 + md5(双数位 salt)) + QQ 密码)
        //     )
        // 注: salt 长度必须为16
     * @param $data
     * @param $decryptSalt
     * @param $code
     * @return mixed
     */
    public function decryptPassword($data,$decryptSalt,$code){
        // 计算单双
        $dSalt = '';
        $sSalt = '';
        for( $i = 0;$i<strlen($decryptSalt);$i++) {
            if( $i%2 === 0) {
                $dSalt .= $decryptSalt[$i];
            }else{
                $sSalt .= $decryptSalt[$i];
            }
        }

        // 计算key
        $keyKey = substr(hash('md5',hash('md5', $decryptSalt . $code, false) . hash('sha512', $dSalt, false) , false),3,16);
        $keyValue = hash('sha512', hash('sha384', $decryptSalt, false) . strlen($code) . $code . hash('md5', $sSalt, false), false);

        // 密码解密
        $decryptPassword = openssl_decrypt($data, 'AES-128-CBC', $keyKey, 0,$decryptSalt);
        $decryptPassword = openssl_decrypt($decryptPassword, 'AES-128-CBC', substr($keyValue,10,16), 0,substr(hash('md5', $decryptSalt, false),0,16));
        return str_replace($keyValue,'',$decryptPassword);
    }
}