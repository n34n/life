<?php

namespace frontend\controllers;

use common\components\WechatCallbackapiTest;

class TokenController extends \yii\web\Controller
{
    public function actionIndex()
    {
        $wechatObj = new WechatCallbackapiTest();
        $wechatObj->valid();
    }

}
