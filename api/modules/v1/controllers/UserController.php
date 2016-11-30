<?php

namespace api\modules\v1\controllers;

use yii;
use yii\rest\ActiveController;
use yii\web\Response;
use yii\filters\auth\QueryParamAuth;
use api\models\User;
use api\modules\v1\models\Images;

class UserController extends ActiveController
{
    public $modelClass = 'api\models\User';

    public $userinfo;

    public function behaviors() {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = ['class' => QueryParamAuth::className()];
        $behaviors['contentNegotiator']['formats'] = ['application/json' => Response::FORMAT_JSON];
        $this->userinfo = isset($_GET['access-token'])?User::getUserInfo($_GET['access-token']):'';
        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();
        // 注销系统自带的实现方法
        unset($actions['index'], $actions['view'], $actions['create'], $actions['update'], $actions['delete']);
        return $actions;
    }

    protected function verbs()
    {
        return [
            'view' => ['GET', 'HEAD'],
            'update' => ['PUT'],
        ];
    }

    /**
     *
     *	@SWG\Get(
     * 		path="/user/{id}?access-token={access_token}",
     * 		tags={"User"},
     * 		operationId="viewUser",
     * 		summary="查看用户信息",
     * 		@SWG\Parameter(
     * 			name="id",
     * 			in="path",
     * 			required=true,
     * 			type="integer",
     * 			description="用户ID",
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
        /*
         * 禁止用户查看其它用户信息
         *
        if($id != $this->userinfo->user_id){
            $data['code'] = 405;
            return $data;
        }
        */

        $model = new User();
        $data['code'] = 10000;
        $data['user']  = $model->findOne($id);
        return $data;
    }

    /**
     *
     *	@SWG\Put(
     * 		path="/user/{id}?access-token={access_token}",
     * 		tags={"User"},
     * 		operationId="updateUser",
     * 		summary="编辑用户信息",
     * 		@SWG\Parameter(
     * 			name="id",
     * 			in="path",
     * 			required=true,
     * 			type="integer",
     * 			description="用户ID",
     * 		),
     *      @SWG\Parameter(
     * 			name="access_token",
     * 			in="path",
     * 			required=true,
     *          type="string",
     * 			description="访问令牌",
     *		),
     *      @SWG\Parameter(
     * 			name="nickname",
     * 			in="formData",
     * 			required=false,
     * 			type="string",
     * 			description="用户昵称",
     * 		),
     *      @SWG\Parameter(
     * 			name="type",
     * 			in="formData",
     * 			required=false,
     * 			type="string",
     * 			description="微信获取昵称时,不用提交type参数,用户在账户内自行修改昵称时type=update",
     * 		),
     * 		@SWG\Parameter(
     * 			name="tags",
     * 			in="formData",
     * 			required=false,
     * 			type="integer",
     * 			description="用户标签,用户修改或调整标签后,按json形式提交,例子中为更好阅读加了回车,实际使用时请不要有空格和回车:<br>[<br>{&quot;id&quot;:1,&quot;name&quot;:&quot;服装&quot;},<br>{&quot;id&quot;:2,&quot;name&quot;:&quot;标签B&quot;}<br>]",
     * 		),
     * 	)
     */
    public function actionUpdate($id)
    {
        if($id != $this->userinfo->user_id){
            $data['code'] = 405;
            return $data;
        }

        $model = new User();
        $data  = $model->updateInfo($this->userinfo);
        return $data;
    }

}