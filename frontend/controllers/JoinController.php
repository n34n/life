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
        $url    = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$appid."&secret=".$secret."&code=".$_GET['code']."&grant_type=authorization_code";
        $this->redirect($url);
    }

}
