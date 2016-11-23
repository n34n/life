<?php

namespace api\modules\v1\controllers;

use yii;
use yii\rest\ActiveController;
use yii\web\Response;

class ArticleController extends ActiveController
{
    public $modelClass = 'api\modules\v1\models\Article';

    public function behaviors() {
        $behaviors = parent::behaviors();
        $behaviors['contentNegotiator']['formats'] = ['application/json' => Response::FORMAT_JSON];
        return $behaviors;
    }

    protected function verbs()
    {
        return [
            'view' => ['GET'],
        ];
    }

    public function actions()
    {
        $actions = parent::actions();
        // 注销系统自带的实现方法
        unset($actions['index'], $actions['create'], $actions['update'], $actions['delete']);
        return $actions;
    }

}
