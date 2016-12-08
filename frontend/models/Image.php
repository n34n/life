<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "image".
 *
 * @property integer $img_id
 * @property integer $project_id
 * @property string $model
 * @property integer $rel_id
 * @property string $o_path
 * @property string $l_path
 * @property string $m_path
 * @property string $s_path
 * @property integer $created_at
 * @property string $created_by
 */
class Image extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'image';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['project_id', 'rel_id', 'created_at'], 'integer'],
            [['model'], 'required'],
            [['model'], 'string', 'max' => 20],
            [['o_path', 'l_path', 'm_path', 's_path'], 'string', 'max' => 128],
            [['created_by'], 'string', 'max' => 45],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'img_id' => 'Img ID',
            'project_id' => 'Project ID',
            'model' => 'Model',
            'rel_id' => 'Rel ID',
            'o_path' => 'O Path',
            'l_path' => 'L Path',
            'm_path' => 'M Path',
            's_path' => 'S Path',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
        ];
    }
}
