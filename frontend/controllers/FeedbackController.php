<?php

namespace frontend\controllers;

use Yii;

class FeedbackController extends \yii\web\Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }

    /*
     * 用户提交反馈,以邮件形式发到指定邮箱
     */
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
            return $this->redirect('/feedback/status?status=SUCC');
        else
            return $this->redirect('/feedback/status?status=FAILSE');
        die();
    }

    public function actionStatus()
    {
        if(isset($_GET['status'])){
            $status = $_GET['status'];
        }else{
            $status = "FAILSE";
        }

        if($status == "SUCC"){
            $img = '<img src="../images_2/thanks.png">';
            $h2  = '意见反馈成功！';
            $p   = '我们将认真处理你的意见反馈';
        }else{
            $img = '';
            $h2  = '抱歉，提交失败！';
            $p   = '感谢你的反馈，请稍后重试';
        }

        return $this->render('status', [
            'img'   => $img,
            'h2'    => $h2,
            'p'     => $p,
        ]);
    }

}
