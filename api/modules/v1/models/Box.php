<?php

namespace api\modules\v1\models;

use Yii;
use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;
use api\modules\v1\models\RelUserProject;

use api\modules\v1\models\Images;
use api\modules\v1\models\Log;

/**
 * This is the model class for table "box".
 *
 * @property integer $box_id
 * @property string $name
 * @property integer $project_id
 * @property integer $created_at
 * @property string $created_by
 * @property integer $updated_at
 * @property string $updated_by
 *
 * @property Project $project
 * @property Item[] $items
 */
class Box extends ActiveRecord
{
    public $keyword;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'box';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'project_id', 'created_by'], 'required'],
            [['project_id', 'created_at', 'updated_at'], 'integer'],
            [['name'], 'string', 'max' => 40],
            [['created_by', 'updated_by'], 'string', 'max' => 45],
            //[['project_id'], 'exist', 'skipOnError' => true, 'targetClass' => Project::className(), 'targetAttribute' => ['project_id' => 'project_id']],
        ];
    }


    public function fields()
    {
        return [
            'box_id',
            'name',
            'item_total'=>function(){
                            $data = (empty($this->item_total))?'':$this->item_total;
                            return $data;
                        },
            'created_at'=>function(){
                            $data = (empty($this->created_at))?'':$this->created_at;
                            return $data;
                        },
            'created_by',
            'img'=>function(){
                        $data = (empty($this->img))?'':$this->img;
                        return $data;
                    },
        ];
    }


    //获取盒子列表
    public static function getList($project_id)
    {
        //$condition = array('r.user_id'=>$user_id);
        $query = self::find()->where(['project_id'=>$project_id]);
        return $query;
    }


    public function search($params){

        $query = $this->find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => Yii::$app->params['pageSize'],
            ],
        ]);

        $query->where(['project_id' => $_GET['project_id']]);
        if(isset($_GET['keyword']) && $_GET['keyword']!="" && ($_GET['keyword']!="{keyword}")){
            $keywords = trim($_GET['keyword']);
            $keyword_list = explode(' ',$keywords);
            $query->andFilterWhere(['or like', 'name', $keyword_list]);

        }

        $query->orderBy("updated_at DESC");

        return $dataProvider;
    }


    //创建盒子
    public function create($user_id,$nickname)
    {
        //检查参数
        if(!isset($user_id,$_POST['project_id'],$_POST['name'],$nickname)){
            $data['code']  = 20000;
            return $data;
        }

        //检查用户与项目是否匹配
        $rel = RelUserProject::checkUserHasProject($user_id,$_POST['project_id']);
        if($rel == 10111){
            $data['code']  = $rel;
            return $data;
        }

        //准备数据,保存数据
        $d = Yii::$app->request->post();
        foreach ($d as $key=>$val){
            if($this->hasProperty($key)){
                $this->$key = $val;
            }
        }
        $this->user_id = $user_id;
        $this->created_by = $nickname;
        $this->save();

        //保存图片
        if(isset($_POST['img_id'])){
            $img = Images::findOne($_POST['img_id']);
            $img->model  = 'box';
            $img->img_id = $_POST['img_id'];
            $img->rel_id = $this->box_id;
            $img->save();
        }

        //日志
        $log = new Log();
        $log->addLog($_POST['project_id'],$this->box_id,$user_id,'box','create',$this->name,$nickname);

        $data['code'] = 10000;
        $data['info'] = $this;
        return $data;
    }


    //更新盒子
    public function updateInfo($user_id,$nickname,$id)
    {
        //验证方法
        if(!Yii::$app->request->isPut)
        {
            $data['code'] = 400;
            return $data;
        }else{
            $params = Yii::$app->request->bodyParams;
            $params['updated_by'] = $nickname;
        }

        //检查参数
        if(!isset($user_id,$id,$params['project_id'],$nickname)){
            $data['code']  = 20000;
            return $data;
        }

        //验证资源是否存在
        $model = $this->findOne(['box_id'=>$id,'project_id'=>$params['project_id']]);
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
        $log->addLog($params['project_id'],$id,$user_id,'box','update',$data['message'],$nickname);

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

        $model->box_id = $id;
        $model->name = $params['name'];
        $model->updated_by = $params['updated_by'];
        $model->save();

        $data['code']    = 10000;
        $data['message'] = $message;
        return $data;
    }


    //删除盒子
    public function remove($user_id,$nickname,$id)
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
        if(!isset($user_id,$id,$params['project_id'],$nickname)){
            $data['code']  = 20000;
            return $data;
        }

        //检查用户是否有权限
        $rel = RelUserProject::checkUserHasProject($user_id,$params['project_id']);
        if($rel == 10111){
            $data['code']  = $rel;
            return $data;
        }

        //验证资源是否存在
        $query = self::findOne(['box_id'=>$id,'project_id'=>$params['project_id']]);
        if(empty($query)){
            $data['code'] = 50000;
            return $data;
        }

        //删除物品及相关
        $items = Item::findAll(['box_id'=>$id]);
        if(!empty($items)){
            foreach ($items as $item){
                $obj = new Item();
                $obj->remove($user_id,$item->item_id);
            }
        }

        //删除图片资源
        $model = $query->toArray();
        if(isset($model['img']['img_id']) && $model['img']['img_id']!=''){
            Images::removeImg($model['img']['img_id']);
        }

        //删除盒子记录
        $query->delete();

        //日志
        $log = new Log();
        $log->addLog($params['project_id'],$id,$user_id,'box','delete',$model['name'],$nickname);

        $data['code'] = 10000;
        return $data;

    }


    //获取图片
    public function getImg()
    {
        return $this->hasOne(Images::className(), ['rel_id' => 'box_id'])->where(['model'=>'box']);
    }

//    /**
//     * @return \yii\db\ActiveQuery
//     */
//    public function getProject()
//    {
//        return $this->hasOne(Project::className(), ['project_id' => 'project_id']);
//    }
//
//    /**
//     * @return \yii\db\ActiveQuery
//     */
//    public function getItems()
//    {
//        return $this->hasMany(Item::className(), ['box_id' => 'box_id']);
//    }
}
