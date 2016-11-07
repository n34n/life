<?php

namespace api\modules\v1\models;

//use Yii;
use yii\db\ActiveRecord;
use yii\web\Link;
use yii\web\Linkable;
use yii\helpers\Url;

/**
 * This is the model class for table "goods".
 *
 * @property integer $id
 * @property string $name
 */
class Goods extends ActiveRecord implements Linkable
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'goods';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'string', 'max' => 100],
        ];
    }

    public function fields()
    {
        return [
            // 字段名和属性名相同
            'id',
            // 字段名为"email", 对应的属性名为"email_address"
            'email' => 'name',
            // 字段名为"name", 值由一个PHP回调函数定义
            'username' => function ($model) {
                return $model->id . ' ' . $model->name;
            },
        ];
    }

    public function extraFields()
    {
        return [
            'profile' => function ($model) {
                return array($model->id,$model->name);
            },
        ];
    }

    public function getLinks()
    {
        return [
            Link::REL_SELF => Url::to(['goods/view', 'id' => $this->id], true),
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
        ];
    }
}
