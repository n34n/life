<?php

namespace api\modules\v1\controllers;

use yii;
use yii\rest\ActiveController;
use yii\web\Response;
use yii\filters\auth\QueryParamAuth;
use yii\data\ActiveDataProvider;

use api\models\User;
use api\modules\v1\models\RelUserProject;
use api\modules\v1\models\Project;
use api\modules\v1\models\Box;
use api\modules\v1\models\Item;
use api\components\Pages;

class ItemController extends ActiveController
{
    public $modelClass = 'api\modules\v1\models\Box';

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
            'index' => ['GET', 'HEAD'],
            'view' => ['GET', 'HEAD'],
            'create' => ['POST'],
            'update' => ['PUT'],
            'delete' => ['POST'],
            'search' => ['GET'],
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
        if(!isset($_GET['project_id'])) {
            $data['code'] = 20000;
            return $data;
        }else{
            $data['code'] = RelUserProject::checkUserHasProject($this->userinfo->user_id,$_GET['project_id']);
            if($data['code'] == 10111) {return $data;}
        }

        $model         = new Item();
        $list          = $model->search(Yii::$app->request->queryParams);

        $data['code']  = 10000;
        $data['list']  = $list->getModels();
        $data['pages'] = Pages::Pages($list);

        return $data;
    }

    //创建物品
    public function actionCreate()
    {
        $model = new Item();
        $data = $model->create($this->userinfo->user_id);
        return $data;
    }

    //更新物品
    public function actionUpdate($id)
    {
        $model = new Item();
        $data  = $model->updateInfo($this->userinfo->user_id,$id);
        return $data;
    }
}
