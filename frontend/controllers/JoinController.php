<?php

namespace frontend\controllers;

use Yii;
use frontend\models\Project;
use frontend\models\RelUserProject;
use frontend\models\User;
use frontend\models\UserAccount;
use yii\web\Session;

class JoinController extends \yii\web\Controller
{
    public $userinfo;

    public $projinfo;

    public function actionIndex()
    {
        $session = Yii::$app->session;

        //return $this->redirect("jsonData://");
        //return $this->redirect("https://itunes.apple.com/cn/app/tao-bao-sui-shi-sui-xiang/id387682726?mt=8&v0=WWW-GCCN-ITSTOP100-FREEAPPS&l=&ign-mpt=uo%3D4");
//        echo urlencode('evernote:///view/76136038/s12/4d971333-8b65-45d6-857b-243c850cabf5/4d971333-8b65-45d6-857b-243c850cabf5/2cd4dc67-1d52-401f-9aad-d5524b646ba2');
//        return;
       //return $this->render('index2');

        //检查参数
        if(!isset($_GET['state'],$_GET['uid'])){
            return $this->redirect("/site/error?code=20000");
        }

        $project_id = $_GET['state'];
        $owner_id   = $_GET['uid'];

        //判断是否合法访问
        $rel = RelUserProject::findOne(['user_id'=>$owner_id,'project_id'=>$project_id,'is_manager'=>1]);
        if(empty($rel)){
            return $this->redirect("/site/error?code=50001");
        }

        //微信AppID和秘钥
        $appid  = Yii::$app->params['wx_appid'];
        $secret = Yii::$app->params['wx_appsecret'];

        //获取access_token和openid
        $url    = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$appid."&secret=".$secret."&code=".$_GET['code']."&grant_type=authorization_code";
        $token_json = file($url);
        $token = json_decode($token_json[0]);

        //重复刷新页面
        if(isset($token->errcode)){
            //40029 页面已失效
            return $this->redirect("/site/error?code=".$token->errcode);
        }

        //获取用户数据
        if(isset($token) && !empty($token->access_token)){
            $access_token   = $token->access_token;
            $openid         = $token->openid;
            $url = "https://api.weixin.qq.com/sns/userinfo?access_token=".$access_token."&openid=".$openid."&lang=zh_CN";
            $member_json = file($url);
            $member = json_decode($member_json[0]);
            //print_r($userinfo);
        }


        //判断用户是否已经加入项目
        $user = UserAccount::findOne(['account'=>$openid]);
        if(!empty($user)){
            $rel  = RelUserProject::findOne(['user_id'=>$user->user_id,'project_id'=>$project_id]);
            if(!empty($rel)){
                return $this->redirect("/join/join?succ=1&user_id=$user->user_id&project_id=$project_id");
            }
        }


        //项目
        $proj  = Project::findOne($project_id);

        //主人
        $owner = User::findOne($owner_id);

        $session->set('member', $member);
        $session->set('proj', $proj);

        return $this->render('index', [
            'owner'    => $owner,
            'proj'     => $proj,
            'member' => $member,
        ]);

    }


    public function actionJoin()
    {
        //return $this->render('join2');

        //已经加入过项目
        if(Yii::$app->request->isGet){
            if(!isset($_GET['succ'],$_GET['user_id'],$_GET['project_id']) && $_GET['succ'] == 1){
                $this->redirect("/site/error?code=20000");
            }

            $user_id = $_GET['user_id'];
            $project_id = $_GET['project_id'];

            $member  = User::findOne($user_id);
            $proj    = Project::findOne($project_id);

            $avatar       = (isset($member->img->s_path) && $member->img->s_path!=null)?$member->img->s_path:'';
            $nickname     = $member->nickname;

            $project_name = $proj->name;
        }


        if(Yii::$app->request->isPost){
            $session = Yii::$app->session;

            $info = User::join();

            if(is_int($info)){
                return $this->redirect("/site/error?code=".$info);
            }

            $member = $session->get('member');
            $proj = $session->get('proj');

            $avatar       = $member->headimgurl;
            $nickname     = $member->nickname;
            $project_name = $proj->name;
        }



        return $this->render('join', [
            'avatar' => $avatar,
            'nickname'   => $nickname,
            'project_name'   => $project_name,
        ]);
    }


    public function actionJump()
    {
        return $this->redirect("https://itunes.apple.com/cn/app/tao-bao-sui-shi-sui-xiang/id387682726?mt=8&v0=WWW-GCCN-ITSTOP100-FREEAPPS&l=&ign-mpt=uo%3D4");
    }


    public function actionError()
    {
        $code = $_GET['code'];
        return $this->render('error', [
            'code'    => $code,
        ]);
    }

    public function actionMail(){
        $mail= Yii::$app->mailer->compose();
        $mail->setTo('info@lifeqx.com');
        $mail->setSubject("[意见反馈] 软件缺陷");
        $mail->setTextBody('你们的软件,在这个页面能不能加上这个那个功能.');   //发布纯文字文本
        //$mail->setHtmlBody("<br>问我我我我我");    //发布可以带html标签的文本
        if($mail->send())
            echo "success";
        else
            echo "failse";
        die();
    }

}
