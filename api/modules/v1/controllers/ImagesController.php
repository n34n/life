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

    /**
     *
     *	@SWG\Post(
     * 		path="/images/upload?access-token={access_token}",
     * 		tags={"Images"},
     * 		operationId="uploadImg",
     * 		summary="上传图片",
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
     * 			name="file",
     * 			in="formData",
     * 			required=true,
     * 			type="file",
     * 			description="要上传的图片文件",
     * 		),
     *      @SWG\Parameter(
     * 			name="model_name",
     * 			in="formData",
     * 			required=true,
     * 			type="string",
     * 			description="模型:avatar,box,item",
     * 		),
     * 		@SWG\Parameter(
     * 			name="rel_id",
     * 			in="formData",
     * 			required=false,
     * 			type="integer",
     * 			description="关联模型的ID,比如上传盒子图片,则关联这个盒子的ID,一般在编辑状态下才会使用",
     * 		),
     * 		@SWG\Response(
     * 			response=200,
     * 			description="成功",
     * 		),
     * 	)
     */
    public function actionUpload()
    {
        $model = new Images();
        $model_name = (isset($_POST['model_name']))?$_POST['model_name']:'item';
        $data = $model->upload($model_name,$this->userinfo->user_id,$this->userinfo->nickname);
        return $data;
    }


    /**
     *
     *	@SWG\Post(
     * 		path="/images/upload-avatar?access-token={access_token}",
     * 		tags={"Images"},
     * 		operationId="uploadAvatar",
     * 		summary="上传头像",
     *      @SWG\Parameter(
     * 			name="access_token",
     * 			in="path",
     * 			required=true,
     *          type="string",
     * 			description="访问令牌",
     *		),
     * 		@SWG\Parameter(
     * 			name="file",
     * 			in="formData",
     * 			required=true,
     * 			type="file",
     * 			description="要上传的图片文件",
     * 		),
     *      @SWG\Parameter(
     * 			name="type",
     * 			in="formData",
     * 			required=false,
     * 			type="string",
     * 			description="微信获取头像时,不用提交type参数,用户在账户内自行上传头像时type=update",
     * 		),
     * 		@SWG\Response(
     * 			response=200,
     * 			description="成功",
     * 		),
     * 	)
     */
    public function actionUploadAvatar()
    {
        $model = new Images();
        $data = $model->uploadAvatar($this->userinfo->user_id,$this->userinfo->nickname);
        return $data;
    }


    /**
     *
     *	@SWG\Delete(
     * 		path="/images/{id}?access-token={access_token}",
     * 		tags={"Images"},
     * 		operationId="deleteImg",
     * 		summary="删除图片",
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
     * 			name="model_name",
     * 			in="formData",
     * 			required=true,
     * 			type="string",
     * 			description="模型:avatar,box,item",
     * 		),
     * 		@SWG\Parameter(
     * 			name="rel_id",
     * 			in="formData",
     * 			required=true,
     * 			type="integer",
     * 			description="关联模型的ID,比如上传盒子图片,则关联这个盒子的ID,一般在编辑状态下才会使用",
     * 		),
     * 		@SWG\Response(
     * 			response=200,
     * 			description="成功",
     * 		),
     * 	)
     */
    public function actionDelete($id)
    {
        $model = new Images();
        $params = Yii::$app->request->bodyParams;
        $model_name = (isset($params['model_name']))?$params['model_name']:'item';
        $data = $model->remove($model_name,$id,$this->userinfo->user_id,$this->userinfo->nickname);
        return $data;
    }

}
