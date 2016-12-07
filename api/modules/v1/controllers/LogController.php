<?php

namespace api\modules\v1\controllers;

use api\modules\v1\models\RelUserProject;
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
            'message'=> ['GET'],
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

    /**
     *
     *	@SWG\Get(
     * 		path="/log?access-token={access_token}&project_id={project_id}&type={type}&parent_id={parent_id}&page={page}",
     * 		tags={"Log"},
     * 		operationId="listLog",
     * 		summary="历史记录",
     * 		@SWG\Parameter(
     * 			name="access_token",
     * 			in="path",
     * 			required=true,
     *          type="string",
     * 			description="访问令牌",
     *		),
     * 		@SWG\Parameter(
     * 			name="project_id",
     * 			in="path",
     * 			required=true,
     * 			type="integer",
     * 			description="项目ID",
     * 		),
     * 		@SWG\Parameter(
     * 			name="type",
     * 			in="path",
     * 			required=true,
     * 			type="string",
     * 			description="盒子历史记录box,物品历史记录item",
     * 		),
     * 		@SWG\Parameter(
     * 			name="parent_id",
     * 			in="path",
     * 			required=true,
     * 			type="integer",
     * 			description="查看盒子操作日志传项目ID,查看物品操作日志传盒子ID",
     * 		),
     * 		@SWG\Parameter(
     * 			name="page",
     * 			in="path",
     * 			required=false,
     * 			type="integer",
     * 			description="当前请求第X页",
     * 		),
     * 	)
     */
    public function actionIndex()
    {
        //检查参数
        if(!isset($_GET['parent_id'],$_GET['project_id'],$_GET['type'],$this->userinfo->user_id)){
            $data['code'] = 20000;
            return $data;
        }

        //检查用户是否有权限访问
        $data['code'] = RelUserProject::checkUserHasProject($this->userinfo->user_id,$_GET['project_id']);
        if($data['code']!=10000){return $data;}

        $model         = new Log();
        $list          = $model->getList($_GET['type']);

        $data['code']  = 10000;
        $data['list']  = $list->getModels();
        $data['pages'] = Pages::Pages($list);

        return $data;
    }

    /**
     *
     *	@SWG\Get(
     * 		path="/log/message?access-token={access_token}&page={page}",
     * 		tags={"Log"},
     * 		operationId="listMessage",
     * 		summary="全局消息",
     * 		@SWG\Parameter(
     * 			name="access_token",
     * 			in="path",
     * 			required=true,
     *          type="string",
     * 			description="访问令牌",
     *		),
     * 		@SWG\Parameter(
     * 			name="page",
     * 			in="path",
     * 			required=false,
     * 			type="integer",
     * 			description="当前请求第X页",
     * 		),
     * 	)
     */
    public function actionMessage()
    {

        $model         = new Log();
        $list          = $model->getMessage($this->userinfo->user_id);

        $data['code']  = 10000;
        $data['list']  = $list->getModels();
        $data['pages'] = Pages::Pages($list);

        return $data;
    }
}
