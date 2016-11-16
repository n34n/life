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
            'created_at',
            'created_by',
            'img',
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
        if(isset($_GET['keyword'])){
            $keywords = trim($_GET['keyword']);
            $keyword_list = explode(' ',$keywords);
            $query->andFilterWhere(['or like', 'name', $keyword_list]);

        }

        return $dataProvider;
    }


    //创建盒子
    public function create($uid)
    {
        //检查参数
        if(!isset($uid,$_POST['project_id'],$_POST['name'],$_POST['created_by'])){
            $data['code']  = 20000;
            return $data;
        }

        //检查用户与项目是否匹配
        $rel = RelUserProject::checkUserHasProject($uid,$_POST['project_id']);
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
        $this->save();

        //保存图片
        if(isset($_POST['img_id'])){
            $img = Images::findOne($_POST['img_id']);
            $img->img_id = $_POST['img_id'];
            $img->rel_id = $this->box_id;
            $img->save();
        }

        //日志
        $log = new Log();
        $log->addLog($_POST['project_id'],$this->box_id,$uid,'box','create',$this->name,$_POST['created_by']);

        $data['code'] = 10000;
        $data['info'] = $this;
        return $data;
    }


    //更新盒子
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
        if(!isset($user_id,$id,$params['project_id'])){
            $data['code']  = 20000;
            return $data;
        }

        //验证资源是否存在
        $model = $this->findOne(['box_id'=>$id,'project_id'=>$params['project_id']]);
        if(!$model){
            $data['code'] = 50000;
            return $data;
        }

        //保存数据
        foreach ($params as $key=>$val){
            if($model->hasProperty($key)){$model->$key = $val;}
        }
        $model->save();

        //保存图片
        if(isset($params['img_id'])){
            $img = Images::findOne($params['img_id']);
            $img->img_id = $params['img_id'];
            $img->rel_id = $id;
            $img->save();
        }

        //日志
        $log = new Log();
        $log->addLog($params['project_id'],$id,$user_id,'box','update',$model->name,$params['updated_by']);

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
