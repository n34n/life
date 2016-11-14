<?php

namespace api\modules\v1\models;

use Yii;
use yii\db\ActiveRecord;
use api\modules\v1\models\RelUserProject;

use yii\web\Link;
use yii\web\Linkable;
use yii\helpers\Url;
use api\modules\v1\models\Images;

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
class Box extends ActiveRecord implements Linkable
{
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

        $data['code'] = 10000;
        $data['info'] = $this;

        return $data;
    }


    //生成链接
    public function getLinks()
    {
        return [
            Link::REL_SELF => Url::to(['/v1/box/view', 'box_id' => $this->box_id], true),
        ];
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
