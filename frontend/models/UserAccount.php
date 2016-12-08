<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "user_account".
 *
 * @property integer $user_id
 * @property string $access_token
 * @property string $account
 * @property string $device
 * @property string $type
 * @property integer $status
 * @property integer $updated_at
 */
class UserAccount extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_account';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'status', 'updated_at'], 'integer'],
            [['access_token'], 'required'],
            [['type'], 'string'],
            [['access_token', 'account', 'device'], 'string', 'max' => 100],
            [['access_token'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_id' => 'User ID',
            'access_token' => 'Access Token',
            'account' => 'Account',
            'device' => 'Device',
            'type' => 'Type',
            'status' => 'Status',
            'updated_at' => 'Updated At',
        ];
    }
}
