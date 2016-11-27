<?php

namespace api\modules\v1\controllers;

use yii\rest\ActiveController;
use yii\web\Response;
use api\models\User;
use api\models\UserAccount;
use api\modules\v1\models\Project;

class TokenController extends ActiveController
{
    public $modelClass = 'api\models\UserAccount';

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['contentNegotiator']['formats'] = ['application/json' => Response::FORMAT_JSON];
        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();
        // 注销系统自带的实现方法
        unset($actions['index'], $actions['update'], $actions['create'], $actions['delete'], $actions['view']);
        return $actions;
    }

    /**
     *
     *	@SWG\Post(
     * 		path="/token/check-access",
     * 		tags={"Token"},
     * 		operationId="checkToken",
     * 		summary="验证用户登录状态",
     *      @SWG\Parameter(
     * 			name="user_id",
     * 			in="formData",
     * 			required=false,
     *          type="integer",
     * 			description="用户ID",
     *		),
     *      @SWG\Parameter(
     * 			name="account",
     * 			in="formData",
     * 			required=false,
     * 			type="string",
     * 			description="账户唯一标识",
     * 		),
     *      @SWG\Parameter(
     * 			name="device",
     * 			in="formData",
     * 			required=false,
     * 			type="string",
     * 			description="设备唯一标识",
     * 		),
     *      @SWG\Parameter(
     * 			name="type",
     * 			in="formData",
     * 			required=false,
     * 			type="string",
     * 			description="登录类型:mobile,email,weixin,weibo,qq",
     * 		),
     * 		@SWG\Response(
     * 			response=200,
     * 			description="成功",
     * 		),
     * 	)
     */
    public function actionCheckAccess()
    {
        $modelClass = $this->modelClass;
        return $modelClass::checkAccess();
    }

    /**
     *
     *	@SWG\Post(
     * 		path="/token/get-token",
     * 		tags={"Token"},
     * 		operationId="getToken",
     * 		summary="获取身份令牌",
     *      @SWG\Parameter(
     * 			name="account",
     * 			in="formData",
     * 			required=true,
     * 			type="string",
     * 			description="账户唯一标识",
     * 		),
     *      @SWG\Parameter(
     * 			name="device",
     * 			in="formData",
     * 			required=true,
     * 			type="string",
     * 			description="设备唯一标识",
     * 		),
     *      @SWG\Parameter(
     * 			name="type",
     * 			in="formData",
     * 			required=true,
     * 			type="string",
     * 			description="登录类型:mobile,email,weixin,weibo,qq",
     * 		),
     *      @SWG\Parameter(
     * 			name="created_by",
     * 			in="formData",
     * 			required=true,
     * 			type="string",
     * 			description="第三方显示名称，或手机号，用于生成项目时做为创建人使用",
     * 		),
     *      @SWG\Parameter(
     * 			name="timestamp",
     * 			in="formData",
     * 			required=true,
     *          type="integer",
     * 			description="时间戳",
     *		),
     *      @SWG\Parameter(
     * 			name="sign",
     * 			in="formData",
     * 			required=true,
     *          type="string",
     * 			description="签名：根据上述字段键名升续后，进行键值连接生成Str，再在Str末尾连接秘钥Key，最后加密md5(Str+Key)",
     *		),
     * 		@SWG\Response(
     * 			response=200,
     * 			description="成功",
     * 		),
     * 	)
     */
    public function actionGetToken()
    {
        $modelClass = $this->modelClass;

        $data = $modelClass::checkSign();

        if($data['code'] == 10000){
            //检查用户是否存在
            $user = $modelClass::checkUserExsit();
            if($user['code'] == 10000) {//(用户::User::存在)处理下方事项

                $data['code']  = 10000;
                $data['isNew'] = 'N';
                $ua = $modelClass::checkUserAccountExsit();

                if($ua['code'] == 10000) {//(用户账户::UserAccount::存在)处理下方事项
                    $data['user']  = $ua['user'];
                }else{//(用户账户::UserAccount::不存在)处理下方事项
                    $ua = new UserAccount();
                    $data['user'] = $ua->create($user['user']->user_id);
                }

                //获取用户默认项目
                $proj = new Project();
                $p = $proj->getDefault($user['user']->user_id);//默认项目数据封装
                $data['project'] = $p;
                return $data;

            }else{//(用户::User::不存在)处理下方事项

                $data['code']  = 10000;
                $data['isNew'] = 'Y';

                //创建用户
                $user           = new User();
                $data['user']   = $user->create();

                //新用户初始化,创建默认项目
                $proj = new Project();
                $p = $proj->createDefault($data['user']['account']->user_id,$data['user']['account']->_nickname);
                $data['project'] = $p;

                return $data;
            }

        }else{
            return $data;
        }
    }

}
