<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "project".
 *
 * @property integer $project_id
 * @property string $name
 * @property integer $type
 * @property integer $owner_id
 * @property integer $box_total
 * @property integer $item_total
 * @property integer $created_at
 * @property string $created_by
 * @property integer $updated_at
 * @property string $updated_by
 */
class Project extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'project';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'owner_id', 'box_total', 'item_total', 'created_at', 'updated_at'], 'integer'],
            [['owner_id'], 'required'],
            [['name'], 'string', 'max' => 40],
            [['created_by', 'updated_by'], 'string', 'max' => 45],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'project_id' => 'Project ID',
            'name' => 'Name',
            'type' => 'Type',
            'owner_id' => 'Owner ID',
            'box_total' => 'Box Total',
            'item_total' => 'Item Total',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }
}
