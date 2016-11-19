<?php

namespace api\modules\v1\models;

use Yii;
use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;
use api\modules\v1\models\RelUserProject;
use api\modules\v1\models\Tag;
use api\modules\v1\models\Images;
use api\modules\v1\models\Log;


/**
 * This is the model class for table "item".
 *
 * @property integer $item_id
 * @property string $name
 * @property integer $project_id
 * @property integer $box_id
 * @property integer $created_at
 * @property string $created_by
 * @property integer $updated_at
 * @property string $updated_by
 *
 * @property Box $box
 * @property Tag[] $tags
 */
class Item extends ActiveRecord
{
    public $move_error=array();
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'item';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['project_id', 'box_id', 'created_at', 'updated_at'], 'integer'],
            [['name'], 'string', 'max' => 40],
            [['created_by', 'updated_by'], 'string', 'max' => 45],
            //[['box_id'], 'exist', 'skipOnError' => true, 'targetClass' => Box::className(), 'targetAttribute' => ['box_id' => 'box_id']],
        ];
    }


    public function fields()
    {
        return [
            'item_id',
            'box_id',
            'project_id',
            'name',
            'user_id',
            'created_at',
            'created_by',
            'updated_at',
            'updated_by',
            'img',
            'tags',
        ];
    }


    //物品列表
    public function search($params){

        $query = $this->find();
        $query->joinWith(['tags']);
        $query->select("item.*, tag.tag");

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => Yii::$app->params['pageSize'],
            ],
        ]);

        $query->where(['project_id' => $_GET['project_id']]);
        if(isset($_GET['keyword'])){
            $keywords = trim($_GET['keyword']);
            $keyword_list = explode(' ',$keywords);
            $query->andFilterWhere(['or like', 'name', $keyword_list]);
            $query->orFilterWhere(['or like', 'tag', $keyword_list]);

        }

        $query->groupBy(["item_id"]);
        $query->orderBy("updated_at DESC");

        return $dataProvider;
    }


    //创建物品
    public function create($uid)
    {
        //检查参数
        if(!isset($uid,$_POST['project_id'],$_POST['box_id'],$_POST['name'],$_POST['created_by'])){
            $data['code']  = 20000;
            return $data;
        }

        //检查用户与项目是否匹配
        $rel = RelUserProject::checkUserHasProject($uid,$_POST['project_id']);
        if($rel == 10111){
            $data['code']  = $rel;
            return $data;
        }

        //标签处理
        if(isset($_POST['tags']) && is_array($_POST['tags'])){
            $tags = $_POST['tags'];
            unset($_POST['tags']);
        }else{
            $data['code']  = 20000;
            return $data;
        }

        //准备数据,保存数据
        $post = Yii::$app->request->post();
        foreach ($post as $key=>$val){
            if($this->hasProperty($key)){
                $this->$key = $val;
            }
        }
        $this->user_id = $uid;
        $this->updated_by = $this->created_by;
        $this->save();

        //保存图片
        if(isset($_POST['img_id'])){
            $img = Images::findOne($_POST['img_id']);
            $img->model  = 'item';
            $img->img_id = $_POST['img_id'];
            $img->rel_id = $this->item_id;
            $img->save();
        }


        //保存标签
        $tag = new Tag();
        foreach ($tags as $_tag){
            $tag->isNewRecord = true;
            $tag->item_id = $this->item_id;
            $tag->tag_id = $_tag['tag_id'];
            $tag->tag = $_tag['tag'];
            $tag->save();
        }


        //日志
        $log = new Log();
        $log->addLog($_POST['box_id'],$this->item_id,$uid,'item','create',$this->name,$_POST['created_by']);

        $data['code'] = 10000;
        $data['info'] = $this;
        return $data;
    }


    //更新物品
    public function updateInfo($user_id,$id)
    {
        //验证方法
        if(!Yii::$app->request->isPut)
        {
            $data['code'] = 400;
            return $data;
        }else{
            $params = Yii::$app->request->bodyParams;
        }

        //检查参数
        if(!isset($user_id,$id,$params['project_id'],$params['box_id'],$params['updated_by'])){
            $data['code']  = 20000;
            return $data;
        }

        //验证资源是否存在
        $model = $this->findOne(['item_id'=>$id,'box_id'=>$params['box_id'],'project_id'=>$params['project_id']]);
        if(!$model){
            $data['code'] = 50000;
            return $data;
        }

        //更新名称
        $data = $this->updateName($params,$model,$id);
        if($data['code'] != 10000){
            return $data;
        }

        //日志
        $log = new Log();
        $log->addLog($params['project_id'],$id,$user_id,'box','update',$data['message'],$params['updated_by']);

        $data['code'] = 10000;
        return $data;
    }


    //更新名称
    protected function updateName($params,$model,$id)
    {
        if($params['name'] == $model->name){
            $data['code'] = 50100;
            return $data;
        }else{
            $message = '名称['.$model->name.'->'.$params['name'].']';
        }

        $model->item_id = $id;
        $model->name = $params['name'];
        $model->updated_by = $params['updated_by'];
        $model->save();

        $data['code']    = 10000;
        $data['message'] = $message;
        return $data;
    }


    //删除物品
    public function remove($user_id,$id)
    {
        //验证方法
        if(!Yii::$app->request->isDelete)
        {
            $data['code'] = 400;
            return $data;
        }else{
            $params = Yii::$app->request->bodyParams;
        }

        //检查参数
        if(!isset($user_id,$id,$params['project_id'],$params['box_id'],$params['updated_by'])){
            $data['code']  = 20000;
            return $data;
        }

        //验证资源是否存在
        $query = self::findOne(['item_id'=>$id,'box_id'=>$params['box_id'],'project_id'=>$params['project_id']]);
        if(empty($query)){
            //无数据
            $data['code'] = 50000;
            return $data;
        }

        $model = $query->toArray();

        //删除图片资源
        if(isset($model['img']['img_id']) && $model['img']['img_id']!=''){
            Images::removeImg($model['img']['img_id']);
        }

        //删除标签
        Tag::deleteAll(['item_id'=>$id]);

        //更新盒子修改时间
        Box::updateAll(['updated_by'=>$params['updated_by']],'box_id=:id',[':id'=>$params['box_id']]);

        //删除物品记录
        $query->delete();

        //日志
        $log = new Log();
        $log->addLog($params['box_id'],$id,$user_id,'item','delete',$model['name'],$params['updated_by']);

        $data['code'] = 10000;
        return $data;

    }


    //移动物品
    public function move($user_id)
    {
        //验证方法
        if(!Yii::$app->request->isPut)
        {
            $data['code'] = 400;
            return $data;
        }else{
            $params = Yii::$app->request->bodyParams;
        }

        //检查参数
        if(!isset($user_id,$params['items'],$params['project_id'],$params['old_box_id'],$params['new_box_id'],$params['updated_by'])){
            $data['code']  = 20000;
            return $data;
        }

        $old_box = Box::findOne(['box_id'=>$params['old_box_id'],'project_id'=>$params['project_id']]);
        $new_box = Box::findOne(['box_id'=>$params['new_box_id'],'project_id'=>$params['project_id']]);


        if(!$old_box || !$new_box){
            $data['code']  = 50001;
            return $data;
        }

        foreach ($params['items'] as $id){
            $this->moveOne($user_id,$id,$old_box,$new_box,$params['updated_by']);
        }

        $data['code'] = 10000;

        if(!empty($this->move_error)){
            $data['error'] = $this->move_error;
        }

        return $data;
    }


    //移动单个物品
    public function moveOne($user_id,$id,$old_box,$new_box,$updated_by)
    {
        //验证移动物品前数据
        $model = self::findOne(['item_id'=>$id, 'box_id'=>$old_box->box_id]);
        if(empty($model)){
            $message = '#'.$id.'从['.$old_box->name.']盒子移出失败';
            $this->move_error[] = $message;
            $log = new Log();
            $log->addLog($old_box->box_id,$id,$user_id,'item','move-out',$message,$updated_by);
            return;
        }

        //移动物品更新数据
        $model->box_id      = $new_box->box_id;
        $model->updated_by  = $updated_by;
        $model->save();

        //日志
        $log = new Log();
        $log->addLog($old_box->box_id,$id,$user_id,'item','move-out',$old_box->name,$updated_by);
        $log = new Log();
        $log->addLog($new_box->box_id,$id,$user_id,'item','move-in',$new_box->name,$updated_by);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBox()
    {
        return $this->hasOne(Box::className(), ['box_id' => 'box_id']);
    }


    public function getImg()
    {
        return $this->hasOne(Images::className(), ['rel_id' => 'item_id'])->where(['model'=>'item']);
    }


    public function getTags()
    {
        return $this->hasMany(Tag::className(), ['item_id' => 'item_id']);
    }
}
