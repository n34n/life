<?php

namespace api\modules\v1\models;

use Yii;
use yii\db\ActiveRecord;
//use yii\web\Link;
//use yii\web\Linkable;
//use yii\helpers\Url;
//use yii\web\IdentityInterface;

/**
 * This is the model class for table "project".
 *
 * @property integer $project_id
 * @property string $name
 * @property integer $type
 * @property integer $created_at
 * @property string $created_by
 * @property integer $updated_at
 * @property string $updated_by
 *
 * @property Box[] $boxes
 */
class Project extends ActiveRecord
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
            [['name', 'type'], 'required'],
            [['type', 'created_at', 'updated_at'], 'integer'],
            [['name'], 'string', 'max' => 40],
            [['created_by', 'updated_by'], 'string', 'max' => 45],
        ];
    }

    public function fields()
    {
        return [
            'project_id',
            'name',
            'type',
/*            'created_by' => function ($model) {
                return $model->id . ' ' . $model->name;
            },*/
        ];
    }

//    public function getLinks()
//    {
//        return [
//            Link::REL_SELF => Url::to(['project/view', 'project_id' => $this->project_id], true),
//        ];
//    }


    /**
     * @return \yii\db\ActiveQuery
     */
/*    public function getBoxes()
    {
        return $this->hasMany(Box::className(), ['project_id' => 'project_id']);
    }*/
    public function getRelUserProject()
    {
        return $this->hasMany(RelUserProject::className(), ['project_id' => 'project_id']);
    }
}
