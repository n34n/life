<?php

namespace frontend\controllers;

class JoinController extends \yii\web\Controller
{
    public function actionIndex()
    {
//        echo urlencode('http://m.lifeqx.com/join');
//        echo '<br>';
//        echo urlencode('http://m.example.com/join');
        //return $this->render('index');
        $appid  = "wxdcda9da53c5d4b0b";
        $secret = "5ec3a56a7cf9a0a9ec74667a3cd05021";

        //获取access_token和openid
        $url    = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$appid."&secret=".$secret."&code=".$_GET['code']."&grant_type=authorization_code";
        $token_json = file($url);
        $token = json_decode($token_json[0]);

        //获取用户数据
        if(isset($token) && !empty($token->access_token)){
            $access_token   = $token->access_token;
            $openid         = $token->openid;
            $url = "https://api.weixin.qq.com/sns/userinfo?access_token=".$access_token."&openid=".$openid."&lang=zh_CN";
            $userinfo_json = file($url);
            $userinfo = json_decode($userinfo_json[0]);
            print_r($userinfo);
        }

    }

}
