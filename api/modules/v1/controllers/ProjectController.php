<?php

namespace api\modules\v1\controllers;

use api\modules\v1\models\RelUserProject;
use yii;
use yii\rest\ActiveController;
use yii\web\Response;
use yii\filters\auth\QueryParamAuth;
use yii\data\ActiveDataProvider;
use api\modules\v1\models\Project;
use api\models\User;
//use api\components\Pages;

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
            'delete' => ['POST'],
        ];
    }

    public function actions()
    {
        $actions = parent::actions();
        // 注销系统自带的实现方法
        unset($actions['index'], $actions['create'], $actions['delete']);
        return $actions;
    }


    //项目列表
    public function actionIndex()
    {
        $modelClass = $this->modelClass;
        $query      = $modelClass::getList($this->userinfo->user_id);

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize'=>Yii::$app->params['pageSize']]
        ]);
    }

    //创建项目
    public function actionCreate()
    {
        $model = new Project();
        $data  = $model->create($this->userinfo->user_id);
        return $data;
    }

//    public function actionUpdate($id)
//    {
////        $rest = \Yii\rest\yii\rest\UpdateAction::className();
////
//        $model = new yii\rest\UpdateAction;
//        //$m = $model->findModel($id);
//        $s = $this->updateScenario;
//        //$data  = $m->run();
//        $modelClass = $this->modelClass;
//        //$data = $modelClass->run();
//        //$data = $modelClass->run();
//
//        return $data;
////        return $rest;
//
////        return [
////            'class' => 'yii\rest\UpdateAction',
////            'modelClass' => $this->modelClass,
////            'checkAccess' => [$this, 'checkAccess'],
////            'scenario' => $this->updateScenario,
////        ];
//    }

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

}
