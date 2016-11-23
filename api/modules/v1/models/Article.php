<?php

namespace api\modules\v1\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "article".
 *
 * @property integer $id
 * @property string $menu_name
 * @property string $content
 * @property integer $display
 * @property integer $created_at
 * @property integer $updated_at
 */
class Article extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'article';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['content'], 'required'],
            [['content'], 'string'],
            [['display', 'created_at', 'updated_at'], 'integer'],
            [['menu_name'], 'string', 'max' => 10],
        ];
    }


}
