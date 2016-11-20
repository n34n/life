<?php

namespace api\modules\v1\controllers;

use yii;
use yii\rest\ActiveController;
use yii\web\Response;
use yii\filters\auth\QueryParamAuth;
use api\modules\v1\models\Project;
use api\models\User;
use api\components\Pages;

class ProjectController extends ActiveController
{
    public $modelClass = 'api\modules\v1\models\Project';

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


    //项目列表
    public function actionIndex()
    {
        if(!isset($_GET['user_id'])){
            $data['code']  = 20000;
            return $data;
        }
        
        if($this->userinfo->user_id!=$_GET['user_id']){
            $data['code']  = 30001;
            return $data;
        }

        $model         = new Project();
        $list          = $model->search($this->userinfo->user_id);

        $data['code']  = 10000;
        $data['list']  = $list->getModels();
        $data['pages'] = Pages::Pages($list);

        return $data;
    }

    //创建项目
    public function actionCreate()
    {
        $model = new Project();
        $data  = $model->create($this->userinfo->user_id);
        return $data;
    }

    //编辑项目
    public function actionUpdate($id)
    {
        $model = new Project();
        $data  = $model->updateInfo($this->userinfo->user_id,$id);
        return $data;
    }

    //设置默认项目
    public function actionSetDefault()
    {
        if(isset($this->userinfo->user_id,$_POST['project_id']))
        {
            $model = new Project();
            $data  = $model->setDefault($this->userinfo->user_id,$_POST['project_id']);
            return $data;
        }else{
            $data['code']  = 20000;
            return $data;
        }
    }

    //编辑项目
    public function actionDelete($id)
    {
        $model = new Project();
        $data  = $model->remove($this->userinfo->user_id,$id);
        return $data;
    }

}
