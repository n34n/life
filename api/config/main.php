<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/statuscode.php'),
    require(__DIR__ . '/key.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'app-api',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'api\controllers',
    'bootstrap' => ['log'],
    'modules' => [
        'v1' => [
            'class' => 'api\modules\v1\Module',
        ],
    ],
    'components' => [
        'request' => [
 //           'csrfParam' => '_csrf-api',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ]
        ],
        'response' => [
            'class' => 'yii\web\Response',
            'on beforeSend' => function ($event) {
                $response = $event->sender;
                if($response->data !== null){
                    $code = Yii::$app->response->statusCode;

                    //代码信息
                    if(isset($response->data['code'])){
                        $response->data['message'] = Yii::$app->params['codes'][$response->data['code']];
                    }

                    $response->data = [
                        'success' => $response->isSuccessful,
                        'status' => $code,//$response->data['code'],
                        'text' => Yii::$app->params['codes'][$code],
                        'data' => $response->data,//['data'],
                    ];
                }
            },
        ],
        'user' => [
            'identityClass' => 'api\models\User',
            'enableAutoLogin' => true,
            'enableSession'=>false
            //'identityCookie' => ['name' => '_identity-api', 'httpOnly' => true],
        ],
        'session' => [
            // this is the name of the session cookie used for login on the api
            'name' => 'advanced-api',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'error',
        ],

        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => true,
            'enableStrictParsing' =>true,
            'rules' => [
                //初始化检查
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/token',
                    'pluralize' => false,
                    'extraPatterns' => [
                        'POST check-access' => 'check-access',
                        'POST get-token' => 'get-token',
                    ],
                ],

                //项目控制
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/project',
                    'pluralize' => false,
                    'extraPatterns' => [
                        'POST set-default' => 'set-default',
                    ],
                ],


                //盒子控制
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/box',
                    'pluralize' => false,
                    'extraPatterns' => [
                        'GET search' => 'search',
                    ],
                ],


                //图片控制
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/images',
                    'pluralize' => false,
                    'extraPatterns' => [
                        'POST upload' => 'upload',
                    ],
                ],


                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/user',
                    //'except' => ['login', 'logout'],
                    'pluralize' => false,
                    'extraPatterns' => [
                        'GET get-token'     => 'get-token',
                        'POST check-access' => 'check-access',
                    ],
                ],
            ],
        ],
    ],
    'params' => $params,
];
