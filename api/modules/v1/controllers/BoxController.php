<?php

namespace api\modules\v1\controllers;

use yii;
use yii\rest\ActiveController;
use yii\web\Response;
use yii\filters\auth\QueryParamAuth;
use yii\data\ActiveDataProvider;

use api\models\User;
use api\modules\v1\models\Project;
use api\modules\v1\models\RelUserProject;
use api\modules\v1\models\Box;
use api\components\Pages;

class BoxController extends ActiveController
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
            'delete' => ['DELETE'],
        ];
    }

    public function actions()
    {
        $actions = parent::actions();
        // 注销系统自带的实现方法
        unset($actions['index'],$actions['view'], $actions['create'], $actions['update'], $actions['delete']);
        return $actions;
    }


    /**
     *
     *	@SWG\Get(
     * 		path="/box?access-token={access_token}&project_id={project_id}&keyword={keyword}&page={page}",
     * 		tags={"Box"},
     * 		operationId="listBox",
     * 		summary="盒子列表|搜索",
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
     * 			name="keyword",
     * 			in="path",
     * 			required=false,
     *          type="string",
     * 			description="搜索关键字:多个关键字中间可用空格隔开",
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
    public function actionIndex()
    {
        if(!isset($_GET['project_id'])) {
            $data['code'] = 20000;
            return $data;
        }else{
            $data['code'] = RelUserProject::checkUserHasProject($this->userinfo->user_id,$_GET['project_id']);
            if($data['code'] == 10111) {return $data;}
        }

        $model         = new Box();
        $list          = $model->search(Yii::$app->request->queryParams);

        $data['code']  = 10000;
        $data['list']  = $list->getModels();
        $data['pages'] = Pages::Pages($list);

        return $data;
    }

    /**
     *
     *	@SWG\Get(
     * 		path="/box/{id}?access-token={access_token}",
     * 		tags={"Box"},
     * 		operationId="viewBox",
     * 		summary="查看盒子",
     * 		@SWG\Parameter(
     * 			name="id",
     * 			in="path",
     * 			required=true,
     * 			type="integer",
     * 			description="盒子ID",
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
    public function actionView($id)
    {

        //检查参数
        if(!isset($id)){
            $data['code']  = 20000;
            return $data;
        }


        $model         = new Box();
        $data['code']  = 10000;
        $data['data']  = $model->findOne($id);

        return $data;
    }


    /**
     *
     *	@SWG\Post(
     * 		path="/box?access-token={access_token}",
     * 		tags={"Box"},
     * 		operationId="createBox",
     * 		summary="添加盒子",
     *      @SWG\Parameter(
     * 			name="access_token",
     * 			in="path",
     * 			required=true,
     *          type="string",
     * 			description="访问令牌",
     *		),
     * 		@SWG\Parameter(
     * 			name="project_id",
     * 			in="formData",
     * 			required=true,
     * 			type="integer",
     * 			description="项目ID",
     * 		),
     *      @SWG\Parameter(
     * 			name="name",
     * 			in="formData",
     * 			required=true,
     * 			type="string",
     * 			description="盒子名称",
     * 		),
     * 		@SWG\Parameter(
     * 			name="img_id",
     * 			in="formData",
     * 			required=false,
     * 			type="integer",
     * 			description="图片ID可以为空,则说明用户未上传盒子图片",
     * 		),
     * 	)
     */
    public function actionCreate()
    {
        $model = new Box();
        $data = $model->create($this->userinfo->user_id,$this->userinfo->nickname);
        return $data;
    }


    /**
     *
     *	@SWG\Put(
     * 		path="/box/{id}?access-token={access_token}",
     * 		tags={"Box"},
     * 		operationId="updateBox",
     * 		summary="编辑盒子",
     * 		@SWG\Parameter(
     * 			name="id",
     * 			in="path",
     * 			required=true,
     * 			type="integer",
     * 			description="盒子ID",
     * 		),
     *      @SWG\Parameter(
     * 			name="access_token",
     * 			in="path",
     * 			required=true,
     *          type="string",
     * 			description="访问令牌",
     *		),
     * 		@SWG\Parameter(
     * 			name="project_id",
     * 			in="formData",
     * 			required=true,
     * 			type="integer",
     * 			description="项目ID",
     * 		),
     *      @SWG\Parameter(
     * 			name="name",
     * 			in="formData",
     * 			required=true,
     * 			type="string",
     * 			description="盒子名称",
     * 		),
     * 	)
     */
    public function actionUpdate($id)
    {
        $model = new Box();
        $data  = $model->updateInfo($this->userinfo->user_id,$this->userinfo->nickname,$id);
        return $data;
    }


    /**
     *
     *	@SWG\Delete(
     * 		path="/box/{id}?access-token={access_token}",
     * 		tags={"Box"},
     * 		operationId="deleteBox",
     * 		summary="删除盒子",
     * 		@SWG\Parameter(
     * 			name="id",
     * 			in="path",
     * 			required=true,
     * 			type="integer",
     * 			description="盒子ID",
     * 		),
     *      @SWG\Parameter(
     * 			name="access_token",
     * 			in="path",
     * 			required=true,
     *          type="string",
     * 			description="访问令牌",
     *		),
     * 		@SWG\Parameter(
     * 			name="project_id",
     * 			in="formData",
     * 			required=true,
     * 			type="integer",
     * 			description="项目ID",
     * 		),
     * 	)
     */
    public function actionDelete($id)
    {
        //echo 'hello';
        $model = new Box();
        $data  = $model->remove($this->userinfo->user_id,$this->userinfo->nickname,$id);
        return $data;
    }

}
