<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "rel_user_project".
 *
 * @property integer $user_id
 * @property integer $project_id
 * @property integer $is_manager
 * @property integer $is_default
 * @property integer $join_at
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
            [['user_id', 'project_id', 'is_manager', 'is_default', 'join_at'], 'integer'],
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
            'is_default' => 'Is Default',
            'join_at' => 'Join At',
        ];
    }
}
