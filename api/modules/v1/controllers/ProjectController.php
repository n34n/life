<?php

namespace api\modules\v1\controllers;

use yii\rest\ActiveController;
use yii\web\Response;
use yii\filters\auth\QueryParamAuth;
use yii\data\ActiveDataProvider;

class ProjectController extends ActiveController
{
    public $modelClass = 'api\modules\v1\models\Project';

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = ['class' => QueryParamAuth::className()];
        $behaviors['contentNegotiator']['formats'] = ['application/json' => Response::FORMAT_JSON];
        return $behaviors;
    }

/*    public function actionIndex()
    {
        //echo "code::".Yii::$app->request->get('suppress_response_code');
        //$modelClass = $this->modelClass;
        //$query = $modelClass::find();
        $data['code'] = 200;
        $data['data'] = '';
        return $data;
    }*/
    public function actions()
    {
        $actions = parent::actions();
        // 注销系统自带的实现方法
        unset($actions['index'], $actions['update'], $actions['create'], $actions['delete'], $actions['view']);
        return $actions;
    }

    public function actionIndex()
    {
        if(isset($_GET['user_id']) && $_GET['user_id']!=''){
            $modelClass = $this->modelClass;
            $query = $modelClass::find()->from(['p'=>'project'])
                ->leftJoin(['r'=>'rel_user_project'],'p.project_id = r.project_id')
                ->where(['r.user_id'=>$_GET['user_id']])
                ->all();
            $data['code'] = 200;
            $data['data'] = $query;
        }else{
            $data['code'] = 401;
            $data['data'] = '';
        }

        return $data;
    }

}
