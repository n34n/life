<?php

namespace api\modules\v1\controllers;

use api\modules\v1\models\Box;
use yii;
use yii\rest\ActiveController;
use yii\web\Response;
use yii\filters\auth\QueryParamAuth;
use yii\data\ActiveDataProvider;

use api\models\User;
use api\modules\v1\models\Project;
use api\modules\v1\models\RelUserProject;
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
        ];
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
     * 		@SWG\Response(
     * 			response=200,
     * 			description="成功",
     * 		),
     * 	)
     */
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
     * 		path="/box?access-token={access_token}&project_id={project_id}&keyword={keyword}",
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
     * 		@SWG\Response(
     * 			response=200,
     * 			description="成功",
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


    //创建盒子
    public function actionCreate()
    {
        $model = new Box();
        $data = $model->create($this->userinfo->user_id);
        return $data;
    }


    //更新盒子
    public function actionUpdate($id)
    {
        $model = new Box();
        $data  = $model->updateInfo($this->userinfo->user_id,$id);
        return $data;
    }


    //删除盒子
    public function actionDelete($id)
    {
        $model = new Box();
        $data  = $model->remove($this->userinfo->user_id,$id);
        return $data;
    }

}
