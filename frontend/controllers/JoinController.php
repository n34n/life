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

//        echo urlencode('https://itunes.apple.com/cn/app/bear-hua-li-shu-xie-bi-ji/id1091189122?mt=12');
//        return;
       //return $this->render('index2');

        //检查参数
        if(!isset($_GET['state'],$_GET['uid'])){
            return $this->redirect("/join/error?code=20000");
        }

        $project_id = $_GET['state'];
        $owner_id   = $_GET['uid'];

        //判断是否合法访问
        $rel = RelUserProject::findOne(['user_id'=>$owner_id,'project_id'=>$project_id,'is_manager'=>1]);
        if(empty($rel)){
            return $this->redirect("/join/error?code=50001");
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
            return $this->redirect("/join/error?code=".$token->errcode);
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
        //已经加入过项目
        if(Yii::$app->request->isGet){
            if(!isset($_GET['succ'],$_GET['user_id'],$_GET['project_id']) && $_GET['succ'] == 1){
                $this->redirect("/join/error?code=20000");
            }

            $user_id = $_GET['user_id'];
            $project_id = $_GET['project_id'];

            $member  = User::findOne($user_id);
            //$member->headimgurl = $member->img->s_path;
            $proj  = Project::findOne($project_id);

            $avatar       = $member->img->s_path;
            $nickname     = $member->nickname;
            $project_name = $proj->name;
        }


        if(Yii::$app->request->isPost){
            $session = Yii::$app->session;

            $info = User::join();

            if(is_int($info)){
                return $this->redirect("/join/error?code=".$info);
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


    public function actionError()
    {
        $code = $_GET['code'];
        return $this->render('error', [
            'code'    => $code,
        ]);
    }

}
