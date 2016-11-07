<?php

namespace api\modules\v1\controllers;

use yii\rest\ActiveController;
use yii\web\Response;
use yii\filters\auth\QueryParamAuth;

class GoodsController extends ActiveController
{
    public $modelClass = 'api\modules\v1\models\Goods';

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class' => QueryParamAuth::className(),
        ];

        $behaviors['contentNegotiator']['formats'] = ['application/json' => Response::FORMAT_JSON];

        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();

        // 禁用"delete" 和 "create" 操作
        unset($actions['delete'], $actions['create']);

        // 使用"prepareDataProvider()"方法自定义数据provider
        //$actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];

        return $actions;
    }

/*    public function prepareDataProvider()
    {
        // 为"index"操作准备和返回数据provider
        return new ActiveDataProvider([
            'query' => Post::find(),
        ]);
    }*/

    public function actionIndex()
    {
        //echo "code::".Yii::$app->request->get('suppress_response_code');
        $data['code'] = 200;
        $data['data'] = new ActiveDataProvider([
            'query' => Post::find(),
        ]);
        return $data;
    }
}