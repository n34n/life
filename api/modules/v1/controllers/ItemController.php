<?php

namespace api\modules\v1\controllers;

use yii;
use yii\rest\ActiveController;
use yii\web\Response;
use yii\filters\auth\QueryParamAuth;
use yii\data\ActiveDataProvider;

use api\models\User;
use api\modules\v1\models\RelUserProject;
use api\modules\v1\models\Project;
use api\modules\v1\models\Box;
use api\modules\v1\models\Item;
use api\components\Pages;

class ItemController extends ActiveController
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
            'move' =>   ['PUT'],
            'delete' => ['DELETE'],
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
     * 		path="/item?access-token={access_token}&project_id={project_id}&keyword={keyword}&tags={tag_ids}&page={page}",
     * 		tags={"Item"},
     * 		operationId="listItem",
     * 		summary="物品列表|搜索",
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
     * 			name="tag_ids",
     * 			in="path",
     * 			required=false,
     *          type="string",
     * 			description="筛选标签ID:标签ID多个时,中间用逗号隔开",
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

        $model         = new Item();
        $list          = $model->search(Yii::$app->request->queryParams);
        $tag_list      = $model->filterTags();

        $data['code']  = 10000;
        $data['list']  = $list->getModels();
        $data['tags']  = $tag_list;
        $data['pages'] = Pages::Pages($list);

        return $data;
    }

    /**
     *
     *	@SWG\Get(
     * 		path="/item/{id}?access-token={access_token}&project_id={project_id}",
     * 		tags={"Item"},
     * 		operationId="viewItem",
     * 		summary="查看物品",
     * 		@SWG\Parameter(
     * 			name="id",
     * 			in="path",
     * 			required=true,
     * 			type="integer",
     * 			description="物品ID",
     * 		),
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
     * 	)
     */
    public function actionView($id)
    {
        if(!isset($id,$_GET['project_id'])) {
            $data['code'] = 20000;
            return $data;
        }else{
            $data['code'] = RelUserProject::checkUserHasProject($this->userinfo->user_id,$_GET['project_id']);
            if($data['code'] == 10111) {return $data;}
        }

        $info          = Item::findOne(['project_id'=>$_GET['project_id'],'item_id'=>$id]);
        if(empty($info)){
            $data['code'] = 50001;
            return $data;
        }
        $data['code']  = 10000;
        $data['info']  = $info;
        return $data;
    }


    /**
     *
     *	@SWG\Post(
     * 		path="/item?access-token={access_token}",
     * 		tags={"Item"},
     * 		operationId="createItem",
     * 		summary="添加物品",
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
     * 		@SWG\Parameter(
     * 			name="box_id",
     * 			in="formData",
     * 			required=true,
     * 			type="integer",
     * 			description="盒子ID",
     * 		),
     *      @SWG\Parameter(
     * 			name="name",
     * 			in="formData",
     * 			required=true,
     * 			type="string",
     * 			description="物品名称",
     * 		),
     *      @SWG\Parameter(
     * 			name="tags",
     * 			in="formData",
     * 			required=true,
     * 			type="string",
     * 			description="标签数据:以json形式提交,例子中为更好阅读加了回车,实际使用时请不要有空格和回车:<br>[<br>{&quot;tag_id&quot;:1,&quot;tag&quot;:&quot;服装&quot;},<br>{&quot;tag_id&quot;:2,&quot;tag&quot;:&quot;鞋靴&quot;}<br>]",
     * 		),
     * 		@SWG\Parameter(
     * 			name="img_id[]",
     * 			in="formData",
     * 			required=true,
     * 			type="array",
     *          @SWG\Items(type="integer"),
     * 			description="图片ID:保存添加的物品时,说明物品图片已经上传并生成ID,关联时每行一个图片ID",
     * 		),
     * 	)
     */
    public function actionCreate()
    {
        $model = new Item();
        $data = $model->create($this->userinfo->user_id,$this->userinfo->nickname);
        return $data;
    }

    /**
     *
     *	@SWG\Put(
     * 		path="/item/{id}?access-token={access_token}",
     * 		tags={"Item"},
     * 		operationId="updateItem",
     * 		summary="编辑物品",
     * 		@SWG\Parameter(
     * 			name="id",
     * 			in="path",
     * 			required=true,
     * 			type="integer",
     * 			description="物品ID",
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
     * 		@SWG\Parameter(
     * 			name="box_id",
     * 			in="formData",
     * 			required=true,
     * 			type="integer",
     * 			description="盒子ID",
     * 		),
     *      @SWG\Parameter(
     * 			name="name",
     * 			in="formData",
     * 			required=true,
     * 			type="string",
     * 			description="物品名称",
     * 		),
     * 	)
     */
    public function actionUpdate($id)
    {
        $model = new Item();
        $data  = $model->updateInfo($this->userinfo->user_id,$this->userinfo->nickname,$id);
        return $data;
    }

    /**
     *
     *	@SWG\Put(
     * 		path="/item/move?access-token={access_token}",
     * 		tags={"Item"},
     * 		operationId="moveItem",
     * 		summary="转移物品",
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
     * 		@SWG\Parameter(
     * 			name="old_box_id",
     * 			in="formData",
     * 			required=true,
     * 			type="integer",
     * 			description="原始盒子ID(转移前)",
     * 		),
     * 		@SWG\Parameter(
     * 			name="new_box_id",
     * 			in="formData",
     * 			required=true,
     * 			type="integer",
     * 			description="目的盒子ID(转以后)",
     * 		),
     *      @SWG\Parameter(
     * 			name="items[]",
     * 			in="formData",
     * 			required=true,
     * 			type="array",
     * 			description="待转移物品ID:多物品转移时,每行一个物品ID",
     * 		),
     * 	)
     */
    public function actionMove()
    {
        $model = new Item();
        $data  = $model->move($this->userinfo->user_id,$this->userinfo->nickname);
        return $data;
    }

    /**
     *
     *	@SWG\Delete(
     * 		path="/item/{id}?access-token={access_token}",
     * 		tags={"Item"},
     * 		operationId="deleteItem",
     * 		summary="删除物品",
     * 		@SWG\Parameter(
     * 			name="id",
     * 			in="path",
     * 			required=true,
     * 			type="integer",
     * 			description="物品ID",
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
     * 		@SWG\Parameter(
     * 			name="box_id",
     * 			in="formData",
     * 			required=true,
     * 			type="integer",
     * 			description="物品ID",
     * 		),
     * 	)
     */
    public function actionDelete($id)
    {
        $model = new Item();
        $data  = $model->remove($this->userinfo->user_id,$this->userinfo->nickname,$id);
        return $data;
    }
}
