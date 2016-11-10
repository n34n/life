<?php

namespace api\modules\v1\controllers;

use yii\rest\ActiveController;
use yii\web\Response;
use api\models\User;
use api\models\UserAccount;
use api\modules\v1\models\Project;

class TokenController extends ActiveController
{
    public $modelClass = 'api\models\UserAccount';

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['contentNegotiator']['formats'] = ['application/json' => Response::FORMAT_JSON];
        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();
        // 注销系统自带的实现方法
        unset($actions['index'], $actions['update'], $actions['create'], $actions['delete'], $actions['view']);
        return $actions;
    }

    //验证用户是否登录
    public function actionCheckAccess()
    {
        $modelClass = $this->modelClass;
        return $modelClass::checkAccess();
    }

    //获取令牌
    public function actionGetToken()
    {
        $modelClass = $this->modelClass;

        $data = $modelClass::checkUserData();
        if($data['code'] == 10000){
            $user = $modelClass::findUser();
            $proj = new Project();
            if($user != null){
                $data['code'] = 10000;
                $data['user'] = $user;
                $_proj = $proj->getDefault($user->user_id);
                $data['project'] = $_proj['data'];
                return $data;
            }else{
                //创建用户
                $user    = new User();
                $user_id = $user->savedata();

                //用户账户表
                $_user = new UserAccount();
                $_user->user_id = $user_id;
                $_user->access_token  = $modelClass::setToken();
                $_user->account = $_POST['account'];
                $_user->device  = $_POST['device'];
                $_user->type    = $_POST['type'];
                $_user->save();

                //项目
                $_POST['user_id'] = $user_id;
                $_POST['type'] = 1;
                $_proj = $proj->createDefault($user_id);

                $data['code']    = 10000;
                $data['user']    = $_user;
                $data['project'] = $_proj['data'];
                return $data;
            }

        }else{
            return $data;
        }
    }

}
