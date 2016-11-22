<?php

namespace api\modules\v1\controllers;


use yii;
use yii\rest\ActiveController;
use yii\web\Response;
use yii\filters\auth\QueryParamAuth;
use api\modules\v1\models\Images;
use api\models\User;


class ImagesController extends ActiveController
{
    public $modelClass = 'api\modules\v1\models\Images';

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
            'upload' => ['POST'],
            'upload-avatar' => ['POST'],
        ];
    }


    public function actions()
    {
        $actions = parent::actions();
        // 注销系统自带的实现方法
        unset($actions['index'], $actions['create'], $actions['update'], $actions['delete']);
        return $actions;
    }


    public function actionUpload()
    {
        $model = new Images();
        $model_name = (isset($_POST['model_name']))?$_POST['model_name']:'item';
        $data = $model->upload($model_name,$this->userinfo->user_id);
        return $data;
    }


    public function actionUploadAvatar()
    {
        $model = new Images();
        $data = $model->uploadAvatar($this->userinfo->user_id);
        return $data;
    }


    public function actionDelete($id)
    {
        $model = new Images();
        $params = Yii::$app->request->bodyParams;
        $model_name = (isset($params['model_name']))?$params['model_name']:'item';
        $data = $model->remove($model_name,$id,$this->userinfo->user_id);
        return $data;
    }



}
