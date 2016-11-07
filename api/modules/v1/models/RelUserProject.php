<?php

namespace api\modules\v1\models;

use Yii;

/**
 * This is the model class for table "rel_user_project".
 *
 * @property integer $user_id
 * @property integer $project_id
 * @property integer $is_manager
 *
 * @property User $user
 */
class RelUserProject extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'rel_user_project';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'project_id', 'is_manager'], 'required'],
            [['user_id', 'project_id', 'is_manager'], 'integer'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'user_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_id' => 'User ID',
            'project_id' => 'Project ID',
            'is_manager' => 'Is Manager',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['user_id' => 'user_id']);
    }
}
