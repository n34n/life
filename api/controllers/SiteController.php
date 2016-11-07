<?php
namespace api\controllers;

use Yii;
use yii\rest\ActiveController;
use yii\web\Response;
use yii\web\Controller;


/**
 * Site controller
 */
class SiteController extends ActiveController
{
    public $modelClass = 'api\modules\v1\models\Goods';
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['contentNegotiator']['formats'] = ['application/json' => Response::FORMAT_JSON];
        return $behaviors;
    }

    public function actionError()
    {
        $error = Yii::app()->errorHandler->error;
        Yii::$app->response->statusCode=404;

        if( $error )
        {
            //$this -> render( 'error', array( 'error' => $error ) );
            $data['code'] = 404;
            $data['error'] = $error;

            return $data;
        }

    }
}
