<?php

namespace api\modules\v1\controllers;

use yii;
use yii\rest\ActiveController;
use yii\web\Response;
use yii\filters\auth\QueryParamAuth;

class ArticleController extends ActiveController
{
    public $modelClass = 'api\modules\v1\models\Article';

    public function behaviors() {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = ['class' => QueryParamAuth::className()];
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

    /**
     *
     *	@SWG\Get(
     * 		path="/article/{id}?access-token={access_token}",
     * 		tags={"Article"},
     * 		operationId="viewArticle",
     * 		summary="查看文章",
     * 		@SWG\Parameter(
     * 			name="id",
     * 			in="path",
     * 			required=true,
     * 			type="integer",
     * 			description="文章ID,具体调用的ID值,请问管理员索取",
     * 		),
     * 		@SWG\Parameter(
     * 			name="access_token",
     * 			in="path",
     * 			required=true,
     *          type="string",
     * 			description="访问令牌",
     *		),
     * 	)
     */

}
