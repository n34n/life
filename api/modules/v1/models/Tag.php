<?php

namespace api\modules\v1\models;

use Yii;
use yii\db\ActiveRecord;
use api\modules\v1\models\Box;
use api\modules\v1\models\Item;

/**
 * This is the model class for table "tag".
 *
 * @property integer $item_id
 * @property string $tag
 *
 * @property Item $item
 */
class Tag extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tag';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['item_id','tag_id'], 'integer'],
            [['tag'], 'string', 'max' => 10],
            //[['item_id'], 'exist', 'skipOnError' => true, 'targetClass' => Item::className(), 'targetAttribute' => ['item_id' => 'item_id']],
        ];
    }


    public function updateInfo($user_id)
    {
        //验证方法
        if(!Yii::$app->request->isPut)
        {
            $data['code'] = 400;
            return $data;
        }else{
            $params = Yii::$app->request->bodyParams;
        }

        //验证参数
        if(!isset($user_id,$params['box_id'],$params['item_id'],$params['updated_by'],$params['tags'])){
            $data['code'] = 20000;
            return $data;
        }


        $item = Item::findOne(['box_id'=>$params['box_id'],'item_id'=>$params['item_id']]);
        if(empty($item)){
            $data['code']  = 50001;
            return $data;
        }

        //清除原标签
        self::removeAll($params['item_id']);

        //更新标签
        $message = '';
        foreach ($params['tags'] as $_tag){
            $this->isNewRecord = true;
            $this->item_id = $item->item_id;
            $this->tag_id = $_tag['tag_id'];
            $this->tag = $_tag['tag'];
            $this->save();
            $message .= '['.$_tag['tag'].']';
        }
        //$message = substr($message,0,-1);

        //盒子物品更新修改时间
        Box::updateAll(['updated_by'=>$params['updated_by']],'box_id=:id',[':id'=>$params['box_id']]);
        Item::updateAll(['updated_by'=>$params['updated_by']],'item_id=:id',[':id'=>$params['item_id']]);

        //日志
        $log = new Log();
        $log->addLog($params['box_id'],$params['item_id'],$user_id,'item','tag-update',$message,$params['updated_by']);

        $data['code'] = 10000;
        return $data;
    }


    public static function removeAll($item_id)
    {
        self::deleteAll(['item_id'=>$item_id]);
    }


    public static function remove($item_id,$tag_id)
    {
        self::findOne(['item_id'=>$item_id,'tag_id'=>$tag_id])->delete();
    }
    

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItem()
    {
        return $this->hasOne(Item::className(), ['item_id' => 'item_id']);
    }
}
