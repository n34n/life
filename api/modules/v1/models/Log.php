<?php

namespace api\modules\v1\models;

use Yii;
use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;
use api\models\User;
use api\modules\v1\models\Project;

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
        //$fields = parent::fields();

        $fields['manager_id']  = 'rel_id';
        $fields['user_id']     = 'user_id';
        $fields['action']      = 'action';
        $fields['message']     = 'message';
        $fields['created_at']  = 'created_at';
        $fields['created_by']  = 'created_by';
        $fields['avatar']      = function(){
                                    $data = (empty($this->avatar))?'':Yii::$app->params['imgServer'].$this->avatar->s_path;
                                    return $data;};

        return $fields;
    }


    public function getList($type){

        $query = $this->find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => Yii::$app->params['pageSize'],
            ],
        ]);

        $query->where(['parent_id' => $_GET['parent_id']])->andWhere(['model'=>$type]);

        $query->orderBy("created_at DESC");

        return $dataProvider;
    }

    public function getMessage($user_id){

        $query = $this->find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => Yii::$app->params['pageSize'],
            ],
        ]);

        $query->where(['rel_id' => $user_id])->orWhere(['user_id' => $user_id])->andFilterWhere(['model'=>'project']);

        $query->orderBy("created_at DESC");

        //echo $query->createCommand()->rawSql;

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

    public function getManager()
    {
        return $this->hasOne(User::className(), ['user_id' => 'rel_id']);
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['user_id' => 'user_id']);
    }

    public function getAvatar()
    {
        return $this->hasOne(Images::className(), ['rel_id'=>'user_id'])->where(['model'=>'avatar'])->select(['s_path']);
    }

    public function getProject()
    {
        return $this->hasOne(Project::className(), ['project_id' => 'parent_id']);
    }
}
