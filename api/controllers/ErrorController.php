<?php

namespace api\controllers;

use yii;

class ErrorController extends \yii\web\Controller
{

    //错误统一成500错误
    public function actionIndex()
    {
        $data['success']    = false;
        $data['code']       = 500;
        $data['message']    = Yii::$app->params['codes'][$data['code']];
        //$data['data']       = '';
        echo  json_encode($data);
        exit;
    }

}
