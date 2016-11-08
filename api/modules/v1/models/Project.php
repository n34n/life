<?php

namespace api\modules\v1\models;

use Yii;
use yii\db\ActiveRecord;
use api\modules\v1\models\RelUserProject;
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
            'created_at',
            'created_by',
            'extra',
        ];
    }

    public function getList($user_id)
    {
        $query = $this->find()->from(['p'=>'project'])
            ->leftJoin(['r'=>'rel_user_project'],'p.project_id = r.project_id')
            ->where(['r.user_id'=>$user_id])
            ->all();
        if(empty($query)){
            $data['code'] = 50000;
        }else{
            $data['code'] = 10000;
            $data['list'] = $query;
        }
        return $data;
    }

    public function create($user_id,$is_default='')
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
                $rel->is_default = ($is_default!='')?$is_default:0;
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

    public function createDefault()
    {
        $_POST['name'] = Yii::$app->params['defaultProject'];
        return $this->create(1);
    }
    
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
    public function getExtra()
    {
        return $this->hasOne(RelUserProject::className(), ['project_id' => 'project_id'])->select('is_default,is_manager');
    }
}
