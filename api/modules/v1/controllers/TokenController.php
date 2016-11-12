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

    //验证用户是否登录
    public function actionCheckAccess()
    {
        $modelClass = $this->modelClass;
        return $modelClass::checkAccess();
    }

    //获取令牌
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
                    $data['user']  = $ua;
                }else{//(用户账户::UserAccount::不存在)处理下方事项
                    $ua = new UserAccount();
                    $data['user'] = $ua->create($user['user']->user_id);
                }

                //获取用户默认项目
                $proj = new Project();
                $p = $proj->getDefault($user['user']->user_id);//默认项目数据封装
                $data['project'] = $p['data'];
                return $data;

            }else{//(用户::User::不存在)处理下方事项

                $data['code']  = 10000;
                $data['isNew'] = 'Y';

                //创建用户
                $user           = new User();
                $data['user']   = $user->create();

                //新用户初始化,创建默认项目
                $proj = new Project();
                $p = $proj->createDefault($data['user']->user_id);
                $data['project'] = $p['data'];

                return $data;
            }

        }else{
            return $data;
        }
    }

}
