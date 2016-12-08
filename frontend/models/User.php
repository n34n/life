<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "user".
 *
 * @property integer $user_id
 * @property string $nickname
 * @property integer $nickname_updated
 * @property integer $avatar_updated
 * @property string $tag_data
 * @property integer $created_at
 * @property integer $updated_at
 */
class User extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['nickname_updated', 'avatar_updated', 'created_at', 'updated_at'], 'integer'],
            [['tag_data'], 'string'],
            [['nickname'], 'string', 'max' => 45],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_id' => 'User ID',
            'nickname' => 'Nickname',
            'nickname_updated' => 'Nickname Updated',
            'avatar_updated' => 'Avatar Updated',
            'tag_data' => 'Tag Data',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
