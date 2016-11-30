<?php

namespace api\modules\v1\models;

use Yii;
use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "log".
 *
 * @property integer $id
 * @property string $model
 * @property integer $parent_id
 * @property integer $rel_id
 * @property integer $user_id
 * @property string $action
 * @property string $message
 * @property integer $created_at
 * @property string $created_by
 */
class Log extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['parent_id', 'rel_id', 'user_id'], 'required'],
            [['parent_id', 'rel_id', 'user_id', 'created_at'], 'integer'],
            [['model'], 'string', 'max' => 20],
            [['action'], 'string', 'max' => 15],
            [['message'], 'string', 'max' => 100],
            [['created_by'], 'string', 'max' => 45],
        ];
    }

    public function fields()
    {
        return [
            'id',
            'parent_id',
            'rel_id',
            'user_id',
            'model',
            'action',
            'message',
            'created_by',
            'created_at',
        ];
    }


    public function getList(){

        $query = $this->find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => Yii::$app->params['pageSize'],
            ],
        ]);

        $query->where(['parent_id' => $_GET['parent_id']]);

        $query->orderBy("created_at DESC");

        return $dataProvider;
    }


    public function addLog($parent_id,$rel_id,$user_id,$model_name,$action,$message,$created_by)
    {
        $this->parent_id    = $parent_id;
        $this->rel_id       = $rel_id;
        $this->user_id      = $user_id;
        $this->model        = $model_name;
        $this->action       = $action;
        $this->message      = $message;
        $this->created_by   = $created_by;
        return $this->save();
    }
}
