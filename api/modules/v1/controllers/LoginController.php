<?php

namespace api\modules\v1\controllers;

use yii\rest\ActiveController;
use yii\web\Response;
use api\models\User;
use api\models\UserAccount;

class LoginController extends ActiveController
{
    public $modelClass    = 'api\models\UserAccount';

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['contentNegotiator']['formats'] = ['application/json' => Response::FORMAT_JSON];
        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index'], $actions['update'], $actions['create'], $actions['delete'], $actions['view']);
        return $actions;
    }

    //验证用户是否登录
    public function actionGetToken()
    {
        $modelClass = $this->modelClass;

        $data = $modelClass::checkUserData();
        if($data['code'] == 200){
            $user = $modelClass::findUser();
            if($user != null){
                $data['code'] = 200;
                $data['data'] = $user;
                return $data;
            }else{
                //创建用户
                $user    = new User();
                $user_id = $user->savedata();
                $user->save();

                unset($_POST['timestamp']);
                $_POST['user_id'] = $user_id;
                $_user = new UserAccount();
                $_user->user_id = $user_id;
                $_user->access_token  = $modelClass::setToken();
                $_user->account = $_POST['account'];
                $_user->device  = $_POST['device'];
                $_user->type    = $_POST['type'];
                $_user->save();

                $data['code'] = 200;
                $data['data'] = $_user;
                return $data;
            }

        }else{
            return $data;
        }
    }

}
