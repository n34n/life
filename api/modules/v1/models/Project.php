<?php

namespace api\modules\v1\models;

use Yii;
use yii\db\ActiveRecord;
use api\modules\v1\models\RelUserProject;

use yii\web\Link;
use yii\web\Linkable;
use yii\helpers\Url;
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
class Project extends ActiveRecord implements Linkable
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
            'extra',
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

    //创建项目
    public function create($user_id,$is_default=0)
    {
        if(isset($user_id,$_POST['name'],$_POST['type'],$_POST['created_by']))
        {
            $this->name = $_POST['name'];
            $this->type = $_POST['type'];
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
        return $this->create($user_id,1);
    }

    //编辑项目
    public function updated($user_id)
    {
        //print_r(Yii::$app->request->isPut);
        print_r(Yii::$app->request->isPut);
         $a =Yii::$app->request->bodyParams;
        //$b = Yii::$app->request->parsers($a);
        print_r($a);
        //print_r($b);

        //print_r($project_id);
        //if(isset($user_id,$_PUT['project_id'],$_PUT['name'],$_PUT['type'],$_PUT['updated_by']))
//        if(isset($user_id,$_POST['project_id'],$_POST['name'],$_POST['type'],$_POST['updated_by']))
//        {
//            $data['code'] = 200;
//            //$model = $this->findOne(['user_id'=>$user_id,'project_id'=>$_POST['project_id']]);
////            $rel = RelUserProject::findOne(['user_id'=>$user_id,'project_id'=>$_POST['project_id']]);
////            if(!empty($rel)) {
////                $model = $this->findOne($_POST['project_id']);
////                $model->name = $_POST['name'];
////                $model->type = $_POST['type'];
////                $model->updated_at = time();
////                $model->updated_by = $_POST['updated_by'];
////                $model->save();
////                $data['code'] = 10000;
////            }else{
////                $data['code'] = 10110;
////            }
//        }else{
//            $data['code'] = 20000;
//        }
       // return $data;
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
    public function getLinks()
    {
        return [
            Link::REL_SELF => Url::to(['/v1/project/view', 'project_id' => $this->project_id], true),
        ];
    }

    //扩展字段
    public function getExtra()
    {
        return $this->hasOne(RelUserProject::className(), ['project_id' => 'project_id']);
    }
}
