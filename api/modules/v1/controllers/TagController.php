<?php

namespace api\modules\v1\controllers;

use yii;
use yii\rest\ActiveController;
use yii\web\Response;
use yii\filters\auth\QueryParamAuth;
use api\models\User;
use api\modules\v1\models\Tag;

class TagController extends ActiveController
{
    public $modelClass = 'api\modules\v1\models\Tag';

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
     *	@SWG\Put(
     * 		path="/tag/1?access-token={access_token}",
     * 		tags={"Tag"},
     * 		operationId="createBox",
     * 		summary="编辑标签",

     *      @SWG\Parameter(
     * 			name="access_token",
     * 			in="path",
     * 			required=true,
     *          type="string",
     * 			description="访问令牌",
     *		),
     * 		@SWG\Parameter(
     * 			name="box_id",
     * 			in="formData",
     * 			required=true,
     * 			type="integer",
     * 			description="盒子ID",
     * 		),
     * 		@SWG\Parameter(
     * 			name="item_id",
     * 			in="formData",
     * 			required=true,
     * 			type="integer",
     * 			description="物品ID",
     * 		),
     *      @SWG\Parameter(
     * 			name="tags",
     * 			in="formData",
     * 			required=true,
     * 			type="string",
     * 			description="标签数据:以json形式提交,例子中为更好阅读加了回车,实际使用时请不要有空格和回车:<br>[<br>{&quot;tag_id&quot;:1,&quot;tag&quot;:&quot;服装&quot;},<br>{&quot;tag_id&quot;:2,&quot;tag&quot;:&quot;鞋靴&quot;}<br>]",
     * 		),
     *
     * 		@SWG\Response(
     * 			response=200,
     * 			description="成功",
     * 		),
     * 	)
     */
    public function actionUpdate()
    {
        $model = new Tag();
        $data = $model->updateInfo($this->userinfo->user_id,$this->userinfo->nickname);
        return $data;
    }
}
