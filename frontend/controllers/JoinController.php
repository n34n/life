<?php

namespace frontend\controllers;

use Yii;
use frontend\models\Project;
use frontend\models\RelUserProject;
use frontend\models\User;
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

//        $userinfo['openid']   = 'odTkbwhEqPaDQweliB9avKJTyIWA';
//        $userinfo['nickname'] = 'Sam';
//        $userinfo['sex']      = 1;
//        $userinfo['language'] = 'zh_CN';
//        $userinfo['city']     = '长宁';
//        $userinfo['province'] = '上海';
//        $userinfo['country']  = '中国';
//        $userinfo['headimgurl'] = 'http://wx.qlogo.cn/mmopen/a0ObXfhfLicMzxGGyTdrYUjZ7qWp7ZlOMju5z2ibGVd2pBcicpNacYg3SqJKVu1jgMCc6PPKniaQOoYy2b324cyf9A/0';


        //项目
        $proj  = Project::findOne($project_id);
        //$proj  = $proj->toArray();

        //邀请人
        $owner = User::findOne($owner_id);
        //$owner = $owner->toArray();

        //$_SESSION['userinfo'] = $userinfo;
        //$_SESSION['projinfo'] = $proj;
        $session->set('member', $member);
        $session->set('proj', $proj);

        //return $this->render('index');
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
            switch ($info)
            {
                case 20000:
                    //参数错误
                    $this->redirect();
                    break;
                case 50001:
                    //项目不存在
                    $this->redirect();
                    break;
                case 400:
                    //错误请求
                    $this->redirect();
                    break;
            }
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
