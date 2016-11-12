<?php

namespace api\modules\v1\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "rel_user_project".
 *
 * @property integer $user_id
 * @property integer $project_id
 * @property integer $is_manager
 *
 * @property User $user
 */
class RelUserProject extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'rel_user_project';
    }


    public function fields()
    {
        return [
            'user_id',
            'project_id',
            'is_default',
            'is_manager',
            //'extra',
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'project_id', 'is_manager'], 'required'],
            [['user_id', 'project_id', 'is_manager'], 'integer'],
            //[['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'user_id']],
        ];
    }

    //加入项目
    public function joinProject()
    {

    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['user_id' => 'user_id']);
    }

    public function getExtra()
    {
        return $this->hasOne(Project::className(), ['project_id' => 'project_id']);
    }
}
