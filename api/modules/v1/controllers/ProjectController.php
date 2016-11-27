<?php

namespace api\modules\v1\controllers;

use api\modules\v1\models\RelUserProject;
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
        if(!empty($this->userinfo)){
            $this->userinfo->nickname = ($this->userinfo->nickname!="")?$this->userinfo->nickname:$this->userinfo->_nickname;
        }
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
            'join' => ['POST'],
        ];
    }

    public function actions()
    {
        $actions = parent::actions();
        // 注销系统自带的实现方法
        unset($actions['index'], $actions['view'], $actions['create'], $actions['update'], $actions['delete']);
        return $actions;
    }


    /**
     *
     *	@SWG\Get(
     * 		path="/project?access-token={access_token}&user_id={user_id}",
     * 		tags={"Project"},
     * 		operationId="listProject",
     * 		summary="项目列表",
     * 		@SWG\Parameter(
     * 			name="access_token",
     * 			in="path",
     * 			required=true,
     *          type="string",
     * 			description="访问令牌",
     *		),
     * 		@SWG\Parameter(
     * 			name="user_id",
     * 			in="path",
     * 			required=true,
     * 			type="integer",
     * 			description="用户ID",
     * 		),
     * 		@SWG\Response(
     * 			response=200,
     * 			description="成功",
     * 		),
     * 	)
     */
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


    /**
     *
     *	@SWG\Get(
     * 		path="/project/{id}?access-token={access_token}&user_id={user_id}",
     * 		tags={"Project"},
     * 		operationId="viewProject",
     * 		summary="查看项目",
     *      @SWG\Parameter(
     * 			name="id",
     * 			in="path",
     * 			required=true,
     * 			type="integer",
     * 			description="项目ID",
     * 		),
     * 		@SWG\Parameter(
     * 			name="access_token",
     * 			in="path",
     * 			required=true,
     *          type="string",
     * 			description="访问令牌",
     *		),
     * 		@SWG\Parameter(
     * 			name="user_id",
     * 			in="path",
     * 			required=true,
     * 			type="integer",
     * 			description="用户ID",
     * 		),
     * 		@SWG\Response(
     * 			response=200,
     * 			description="成功",
     * 		),
     * 	)
     */
    public function actionView($id)
    {
        //检查用户是否权限访问项目
        $code = RelUserProject::checkUserHasProject($this->userinfo->user_id,$id);
        if($code != 10000){
            $data['code'] = $code;
            return $data;
        }

        $data         = Project::findOne($id);
        return $data;
    }


    /**
     *
     *	@SWG\Post(
     * 		path="/project?access-token={access_token}",
     * 		tags={"Project"},
     * 		operationId="createProject",
     * 		summary="添加项目",
     *      @SWG\Parameter(
     * 			name="access_token",
     * 			in="path",
     * 			required=true,
     *          type="string",
     * 			description="访问令牌",
     *		),
     *      @SWG\Parameter(
     * 			name="name",
     * 			in="formData",
     * 			required=true,
     * 			type="string",
     * 			description="项目名称",
     * 		),
     * 		@SWG\Parameter(
     * 			name="type",
     * 			in="formData",
     * 			required=true,
     * 			type="integer",
     * 			description="项目类型:1为单人项目，2为多人项目",
     * 		),
     * 		@SWG\Response(
     * 			response=200,
     * 			description="成功",
     * 		),
     * 	)
     */
    public function actionCreate()
    {
        $model = new Project();
        $data  = $model->create($this->userinfo->user_id,$this->userinfo->nickname);
        return $data;
    }

    /**
     *
     *	@SWG\Put(
     * 		path="/project/{id}?access-token={access_token}",
     * 		tags={"Project"},
     * 		operationId="updateProject",
     * 		summary="编辑项目",
     *      @SWG\Parameter(
     * 			name="id",
     * 			in="path",
     * 			required=true,
     * 			type="integer",
     * 			description="项目ID",
     * 		),
     *      @SWG\Parameter(
     * 			name="access_token",
     * 			in="path",
     * 			required=true,
     *          type="string",
     * 			description="访问令牌",
     *		),
     *      @SWG\Parameter(
     * 			name="name",
     * 			in="formData",
     * 			required=false,
     * 			type="string",
     * 			description="项目名称",
     * 		),
     * 		@SWG\Parameter(
     * 			name="type",
     * 			in="formData",
     * 			required=false,
     * 			type="integer",
     * 			description="项目类型:1为单人项目，2为多人项目",
     * 		),
     * 		@SWG\Response(
     * 			response=200,
     * 			description="成功",
     * 		),
     * 	)
     */
    public function actionUpdate($id)
    {
        $model = new Project();
        $data  = $model->updateInfo($this->userinfo->user_id,$this->userinfo->nickname,$id);
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


    /**
     *
     *	@SWG\Post(
     * 		path="/project/join?access-token={access_token}",
     * 		tags={"Project"},
     * 		operationId="joinProject",
     * 		summary="成员加入项目",
     *      @SWG\Parameter(
     * 			name="access_token",
     * 			in="path",
     * 			required=true,
     *          type="string",
     * 			description="访问令牌",
     *		),
     *      @SWG\Parameter(
     * 			name="project_id",
     * 			in="formData",
     * 			required=true,
     * 			type="integer",
     * 			description="项目ID",
     * 		),
     * 		@SWG\Parameter(
     * 			name="manager_id",
     * 			in="formData",
     * 			required=true,
     * 			type="integer",
     * 			description="项目管理员ID",
     * 		),
     * 		@SWG\Response(
     * 			response=200,
     * 			description="成功",
     * 		),
     * 	)
     */
    public function actionJoin()
    {
        $model = new Project();
        $data  = $model->join($this->userinfo);
        return $data;
    }

    /**
     *
     *	@SWG\Delete(
     * 		path="/project/{id}?access-token={access_token}",
     * 		tags={"Project"},
     * 		operationId="updateProject",
     * 		summary="删除项目",
     *      @SWG\Parameter(
     * 			name="id",
     * 			in="path",
     * 			required=true,
     * 			type="integer",
     * 			description="项目ID",
     * 		),
     *      @SWG\Parameter(
     * 			name="access_token",
     * 			in="path",
     * 			required=true,
     *          type="string",
     * 			description="访问令牌",
     *		),
     * 		@SWG\Response(
     * 			response=200,
     * 			description="成功",
     * 		),
     * 	)
     */
    public function actionDelete($id)
    {
        $model = new Project();
        $data  = $model->remove($this->userinfo,$id);
        return $data;
    }

}
