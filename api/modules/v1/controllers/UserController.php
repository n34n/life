<?php

namespace api\modules\v1\controllers;

use yii\rest\ActiveController;
use yii\helpers\ArrayHelper;
use yii\web\Response;
use yii\filters\auth\QueryParamAuth;

class UserController extends ActiveController
{
    public $modelClass = 'api\models\User';

    public function behaviors() {
        $behaviors = parent::behaviors();

/*        $behaviors['authenticator'] = [
            'class' => QueryParamAuth::className(),
        ];*/

        $behaviors['contentNegotiator']['formats'] = ['application/json' => Response::FORMAT_JSON];

        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();

        // 禁用"delete" 和 "create" 操作
        //unset($actions['delete'], $actions['create']);

        // 使用"prepareDataProvider()"方法自定义数据provider
        //$actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];

        return $actions;
    }

/*    public function beforeAction($event)
    {
        echo $action = $event->action->id;
        //die();
        if (isset($this->actions[$action])) {
            $verbs = $this->actions[$action];
        } elseif (isset($this->actions['*'])) {
            $verbs = $this->actions['*'];
        } else {
            return $event->isValid;
        }

        $verb = Yii::$app->getRequest()->getMethod();
        $allowed = array_map('strtoupper', $verbs);
        if (!in_array($verb, $allowed)) {
            $event->isValid = false;
            // http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.7
            Yii::$app->getResponse()->getHeaders()->set('Allow', implode(', ', $allowed));
            throw new MethodNotAllowedHttpException('Method Not Allowed. This url can only handle the following request methods: ' . implode(', ', $allowed) . '.');
        }

        return $event->isValid;
    }*/

/*    public function prepareDataProvider()
    {
        // 为"index"操作准备和返回数据provider
    }*/

    public function actionLogin()
    {

    }

    //获取令牌
    public function actionGetToken()
    {
        $modelClass = $this->modelClass;
        return $modelClass::getToken();
    }

    //验证用户是否登录
    public function actionCheckAccess()
    {
        $modelClass = $this->modelClass;
        return $modelClass::checkAccess();
    }
}