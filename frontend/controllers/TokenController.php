<?php

namespace frontend\controllers;

use common\components\Wechat;

class TokenController extends \yii\web\Controller
{
    public function actionIndex()
    {
        define("TOKEN", "lifeQx2016withSamsTeam"); //TOKEN值
        $wechatObj = new Wechat();
        $wechatObj->valid();
    }

}
