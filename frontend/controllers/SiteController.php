<?php

namespace frontend\controllers;

use Yii;

class SiteController extends \yii\web\Controller
{
    public $layout = "web";

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionError()
    {
        $code = $_GET['code'];
        return $this->render('error', [
            'code'    => $code,
        ]);
    }

}
