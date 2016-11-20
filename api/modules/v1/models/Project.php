<?php

namespace api\modules\v1\models;

use Yii;
use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;
use api\modules\v1\models\RelUserProject;

use yii\web\BadRequestHttpException;
//use yii\web\Link;
//use yii\web\Linkable;
//use yii\helpers\Url;


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
class Project extends ActiveRecord //implements Linkable
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
            'created_at',
            'created_by',
            'rel',
        ];
    }


    //获取项目列表
    public static function getList($user_id)
    {
        $condition = isset($user_id)?array('r.user_id'=>$user_id):'';
        $query = self::find()->from(['p'=>'project'])
            ->leftJoin(['r'=>'rel_user_project'],'p.project_id = r.project_id')
            ->where($condition);

        return $query;
    }


    public function search($user_id){

        $query = $this->find();
        $query->joinWith(['rel']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => Yii::$app->params['pageSize'],
            ],
        ]);

        $query->where(['user_id' => $user_id]);

        $query->orderBy("updated_at DESC");

        return $dataProvider;
    }


    //创建项目
    public function create($user_id,$type=1,$is_default=0)
    {

        $type = isset($_POST['type'])?$_POST['type']:$type;
        if(isset($user_id,$_POST['name'],$type,$_POST['created_by']))
        {
            $this->name = $_POST['name'];
            $this->type = $type;
            $this->created_at = time();
            $this->created_by = $_POST['created_by'];
            if($this->save()){
                $rel = new RelUserProject();
                $rel->user_id = $user_id;
                $rel->project_id = $this->getPrimaryKey();
                $rel->is_manager = 1;
                $rel->is_default = ($is_default==1)?1:0;
                if($rel->save()){
                    $data['code'] = 10000;
                    $data['data'] = $rel;
                }else{
                    $data['code'] = 10002;
                }
            }else{
                $data['code'] = 10001;
            }
        }else{
            $data['code'] = 20000;
        }
        return $data;
    }


    //新建用户时,创建默认项目
    public function createDefault($user_id)
    {
        $_POST['name'] = Yii::$app->params['defaultProject'];
        return $this->create($user_id,1,1);
    }


    //更新项目
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
        if(!isset($user_id,$id,$params['updated_by'])){
            $data['code']  = 20000;
            return $data;
        }

        //验证资源是否存在
        $model = $this->findOne($id);
        if(!$model){
            $data['code'] = 50000;
            return $data;
        }

        //验证权限
        $model = RelUserProject::findOne(['project_id'=>$id,'user_id'=>$user_id]);
        if($model->is_manager != 1){
            $data['code'] = 10111;
            return $data;
        }

        //更新名称
        if(isset($params['name'])){
            $data = $this->updateName($user_id,$id,$params,$model);
            if($data['code'] != 10000){
                return $data;
            }
        }

        //更新类型
        if(isset($params['type'])){
            $data = $this->updateType($user_id,$id,$params,$model);
            if($data['code'] != 10000){
                return $data;
            }
        }

        $data['code'] = 10000;
        return $data;
    }


    //更新名称
    protected function updateName($user_id,$id,$params,$model)
    {
        if($params['name'] == $model->name){
            $data['code'] = 50100;
            return $data;
        }else{
            $message = '名称['.$model->name.'->'.$params['name'].']';
        }

        $model->project_id = $id;
        $model->name = $params['name'];
        $model->updated_by = $params['updated_by'];
        $model->save();

        //日志
        $log = new Log();
        $log->addLog($params['project_id'],0,$user_id,'project','update-name',$message,$params['updated_by']);

        $data['code']    = 10000;
        return $data;
    }


    //更新项目类型
    protected function updateType($user_id,$id,$params,$model)
    {
        if($params['type'] == $model->type){
            $data['code'] = 50100;
            return $data;
        }

        switch ($params['type'])
        {
            case 1:
                //将其他用户踢出项目
                RelUserProject::deleteAll(['project_id'=>$id,'is_manager'=>0]);
                $message = "从多人项目转成单人项目";
                break;
            case 2:
                $message = "从单人项目转成多人项目";
                break;
            default:
                $data['code'] = 50100;
                return $data;
                break;
        }

        //更新项目类型
        $model->project_id = $id;
        $model->type = $params['type'];
        $model->updated_by = $params['updated_by'];
        $model->save();

        //日志
        $log = new Log();
        $log->addLog($params['project_id'],0,$user_id,'project','update-type',$message,$params['updated_by']);

        $data['code']    = 10000;
        return $data;
    }


    //设置默认项目
    public function setDefault($user_id,$project_id)
    {
        $rel = RelUserProject::findOne(['user_id'=>$user_id,'project_id'=>$project_id]);
        if(!empty($rel)){
            $rel->is_default = 1;
            $rel->save();
            RelUserProject::updateAll(['is_default'=>0],
                'user_id=:user_id And project_id<>:project_id',
                [':user_id'=>$user_id,':project_id'=>$project_id]);
            $data['code'] = 10000;
        }else{
            $data['code'] = 10110;
        }
        return $data;
    }


    //获取默认项目
    public function getDefault($user_id)
    {
        if(isset($user_id)){
           // $modelClass = $this->modelClass;
            $query = $this->find()->from(['p'=>'project'])
                ->leftJoin(['r'=>'rel_user_project'],'p.project_id = r.project_id')
                ->where(['r.user_id'=>$user_id,'r.is_default'=>1])
                ->one();
            if(empty($query)){
                //无数据
                $data['code'] = 50000;
                $data['data'] = '';
            }else{
                $data['code'] = 10000;
                $data['data'] = $query;
            }
        }else{
            $data['code'] = 20000;
        }
        return $data;
    }

    //生成链接
/*    public function getLinks()
    {
        return [
            Link::REL_SELF => Url::to(['/v1/project/view', 'project_id' => $this->project_id], true),
        ];
    }*/


    //扩展字段
    public function getRel()
    {
        return $this->hasOne(RelUserProject::className(), ['project_id' => 'project_id'])->where(['user_id'=>$_GET['user_id']]);
    }
}
