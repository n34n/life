<?php

namespace api\modules\v1\controllers;

use yii;
use yii\rest\ActiveController;
use yii\web\Response;
use yii\filters\auth\QueryParamAuth;
use api\models\User;
use api\modules\v1\models\Images;

class UserController extends ActiveController
{
    public $modelClass = 'api\models\User';

    public $userinfo;

    public function behaviors() {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = ['class' => QueryParamAuth::className()];
        $behaviors['contentNegotiator']['formats'] = ['application/json' => Response::FORMAT_JSON];
        $this->userinfo = isset($_GET['access-token'])?User::getUserInfo($_GET['access-token']):'';
        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();
        // 注销系统自带的实现方法
        unset($actions['index'], $actions['view'], $actions['create'], $actions['update'], $actions['delete']);
        return $actions;
    }

    protected function verbs()
    {
        return [
            'view' => ['GET', 'HEAD'],
            'update' => ['PUT'],
        ];
    }

    //查看用户信息
    public function actionView($id)
    {
        if($id != $this->userinfo->user_id){
            $data['code'] = 405;
            return $data;
        }
        $model = new User();
        $data['code'] = 10000;
        $data['user']  = $model->findOne($id);
        return $data;
    }

    //获取令牌
    public function actionUpdate()
    {
        $model = new User();
        $data  = $model->updateInfo($this->userinfo->user_id);
        return $data;
    }

    //验证用户是否登录
    public function actionCheckAccess()
    {
        $modelClass = $this->modelClass;
        return $modelClass::checkAccess();
    }
}