<?php

namespace frontend\controllers;

use Yii;

class FeedbackController extends \yii\web\Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionSubmit()
    {
        if(!isset($_POST['type'],$_POST['des']) && $_POST['des']==""){
            return $this->redirect("");
        }

        $type = $_POST['type'];
        $des  = $_POST['des'];
        
        $mail= Yii::$app->mailer->compose();
        $mail->setTo('info@lifeqx.com');
        $mail->setSubject("[意见反馈] ".$type);
        $mail->setTextBody($des);   //发布纯文字文本
        //$mail->setHtmlBody("<br>问我我我我我");    //发布可以带html标签的文本
        if($mail->send())
            echo "SUCC";
        else
            echo "FAILSE";
        die();
    }

}
