<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
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
            //'csrfParam' => '_csrf-api',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ]
        ],
        'response' => [
            'class' => 'yii\web\Response',
            'on beforeSend' => function ($event) {
                $response = $event->sender;
                //print_r($response->data);exit;

/*                if ($response->data !== null) {
                    if(Yii::$app->response->statusCode != 200){
                        $response->data = [
                            'success' => $response->isSuccessful,
                            'code' => Yii::$app->response->statusCode,
                            'message' => Yii::$app->params['codes'][Yii::$app->response->statusCode],
                            'data' => '',
                        ];
                    }else{
                        $response->data = [
                            'success' => $response->isSuccessful,
                            'code' => $response->data['code'],
                            'message' => Yii::$app->params['codes'][$response->data['code']],
                            'data' => $response->data['data'],
                        ];
                    }
                }*/

                if($response->data !== null){
                    $response->data = [
                        'success' => $response->isSuccessful,
                        'code' => $response->data['code'],
                        'message' => Yii::$app->params['codes'][$response->data['code']],
                        'data' => $response->data['data'],
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
                //用户登录
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/login',
                    'pluralize' => false,
                    'extraPatterns' => [
                        'POST get-token' => 'get-token',
                    ],
                ],
                //项目控制
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/project',
                    'pluralize' => false,
                ],
                //项目控制
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/projects',
                    'pluralize' => false,
                ],

                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => ['v1/goods']
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
