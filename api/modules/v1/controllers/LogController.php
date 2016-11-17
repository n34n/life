<?php

namespace api\modules\v1\controllers;

class LogController extends \yii\web\Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }

}
