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
//        echo urlencode('http://m.lifeqx.com/join?uid=1');
//        return;
//        echo '<br>';
//        echo urlencode('http://m.example.com/join');
        //return $this->render('index');


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
                return $this->redirect("/join/error?code=10112");
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
        $session = Yii::$app->session;

        $info = User::join();

        if(is_int($info)){
//            switch ($info)
//            {
//                case 20000:
//                    //参数错误
//                    $this->redirect();
//                    break;
//                case 50001:
//                    //项目不存在
//                    $this->redirect();
//                    break;
//                case 10112:
//                    $this->redirect();
//                    break;
//                case 400:
//                    //错误请求
//                    $this->redirect();
//                    break;
//            }
            return $this->redirect("/join/error?code=".$info);
        }


        return $this->render('join', [
            'info'     => $info,
            'member'   => $session->get('member'),
            'proj'   => $session->get('proj'),
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
