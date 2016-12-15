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

        $content = "";

        if(isset($_POST['username'])){
            $content .= "姓名: ".$_POST['username']."<br/>";
        }


        if(isset($_POST['email'])){
            $content .= "邮箱: ".$_POST['email']."<br/><br/>";
        }

        $type = $_POST['type'];

        $content .= $_POST['des'];



        $mail= Yii::$app->mailer->compose();
        $mail->setTo('info@lifeqx.com');
        $mail->setSubject("[意见反馈] ".$type);
        //$mail->setTextBody($des);   //发布纯文字文本
        $mail->setHtmlBody($content);    //发布可以带html标签的文本
        if($mail->send())
            echo "SUCC";
        else
            echo "FAILSE";
        die();
    }

}
