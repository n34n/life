<?php

namespace api\modules\v1\controllers;

use yii;
use yii\rest\ActiveController;
use yii\web\Response;
use yii\filters\auth\QueryParamAuth;
use api\models\User;
use api\modules\v1\models\Log;
use api\components\Pages;

class LogController extends ActiveController
{
    public $modelClass = 'api\modules\v1\models\Log';

    public $userinfo;

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = ['class' => QueryParamAuth::className()];
        $behaviors['contentNegotiator']['formats'] = ['application/json' => Response::FORMAT_JSON];
        $this->userinfo = isset($_GET['access-token'])?User::getUserInfo($_GET['access-token']):'';
        return $behaviors;
    }


    protected function verbs()
    {
        return [
            'index'  => ['GET', 'HEAD'],
            'create' => ['POST'],
            'delete' => ['DELETE'],
        ];
    }


    public function actions()
    {
        $actions = parent::actions();
        // 注销系统自带的实现方法
        unset($actions['index'], $actions['create'], $actions['update'], $actions['delete']);
        return $actions;
    }

    //物品列表
    public function actionIndex()
    {
        if(!isset($_GET['parent_id'])){
            $data['code'] = 20000;
            return $data;
        }

        $model         = new Log();
        $list          = $model->getList();

        $data['code']  = 10000;
        $data['list']  = $list->getModels();
        $data['pages'] = Pages::Pages($list);

        return $data;
    }
}
