<?php

namespace frontend\controllers;

class SiteController extends \yii\web\Controller
{
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
