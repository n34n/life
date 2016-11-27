<?php

namespace api\modules\v1\models;

use Yii;
use yii\db\ActiveRecord;
use common\components\Upload;
use api\modules\v1\models\Box;
use api\modules\v1\models\Item;
use api\models\User;

/**
 * This is the model class for table "image".
 *
 * @property integer $img_id
 * @property string $model
 * @property integer $rel_id
 * @property string $o_path
 * @property string $l_path
 * @property string $m_path
 * @property string $s_path
 */
class Images extends ActiveRecord
{

    public $file;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'image';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['model'], 'required'],
            [['rel_id', 'project_id'], 'integer'],
            [['model'], 'string', 'max' => 20],
            [['o_path', 'l_path', 'm_path', 's_path'], 'string', 'max' => 100],
        ];
    }


    public function fields()
    {
        return [
            'img_id',
            'model',
            'rel_id',
            'o_path' => function(){
                $data = (empty($this->o_path))?'':Yii::$app->params['imgServer'].$this->o_path;
                return $data;
             },
            'l_path' => function(){
                $data = (empty($this->l_path))?'':Yii::$app->params['imgServer'].$this->l_path;
                return $data;
            },
            'm_path' => function(){
                $data = (empty($this->m_path))?'':Yii::$app->params['imgServer'].$this->m_path;
                return $data;
            },
            's_path' => function(){
                $data = (empty($this->s_path))?'':Yii::$app->params['imgServer'].$this->s_path;
                return $data;
            },
        ];
    }


    //上传盒子和物品图片
    public function upload($model_name='item',$user_id)
    {
        //检查参数
        if(!isset($model_name,$_POST['project_id'],$_POST['created_by'])){
            $data['code']  = 20000;
            return $data;
        }

        if(isset($_POST['project_id'],$_POST['rel_id'])){
            $this->rel_id = $_POST['rel_id'];
            if($model_name == 'item'){
                $query = Item::findOne(['project_id'=>$_POST['project_id'], 'item_id'=>$this->rel_id]);
                $parent_id = $query->box_id;
                $rel_id    = $query->item_id;
            }else{
                $query = Box::findOne(['project_id'=>$_POST['project_id'], 'box_id'=>$this->rel_id]);
                $parent_id = $query->project_id;
                $rel_id    = $query->box_id;
            }

            if(empty($query)){
                $data['code']  = 50001;
                return $data;
            }else{
                if(isset($query->img->img_id) && $query->img->img_id!=""){
                    $this->removeImg($query->img->img_id);
                }
            }
        }

        //保存文件
        $f = new Upload();
        $f->createInstance('file');
        $file   = $f->saveFile();
        if(!is_array($file)){return $data['code']=$file;}

        //生成缩略图
        $img_l = $f->thumb($file,'l',600,600);
        $img_m = $f->thumb($file,'m',300,300);
        $img_s = $f->thumb($file,'s',120,120);

        //图片库关联
        $this->model     = $model_name;
        $this->project_id= $_POST['project_id'];
        $this->created_by= $_POST['created_by'];
        $this->o_path    = $file['path'].$file['file'];
        $this->l_path    = $img_l;
        $this->m_path    = $img_m;
        $this->s_path    = $img_s;
        //$this->rel_id    = (isset($_POST['rel_id']))?$_POST['rel_id']:0;
        $this->save();

        //日志
        if(isset($parent_id,$rel_id)){
            $log = new Log();
            $log->addLog($parent_id,$rel_id,$user_id,$model_name,'img-upload','上传图片',$this->created_by);
        }

        //返回客户端数据
        $data['code']    = 10000;
        $data['images']  = $this;

        return $data;
    }


    //上传头像
    public function uploadAvatar($user_id)
    {
        //验证参数
        if(!isset($user_id,$_POST['created_by'])){
            $data['code']  = 20000;
            return $data;
        }

        //验证数据是否存在
        $user = User::findOne(['user_id'=>$user_id]);
        if(empty($user)){
            $data['code']  = 50001;
            return $data;
        }

        //检查头像是否存在,如果存在则删除头像
        $img = self::findOne(['rel_id'=>$user_id,'model'=>'avatar']);
        if(!empty($img)){
            $this->removeImg($img->img_id);
        }

        //保存文件
        $f = new Upload();
        $f->createInstance('file');
        $file   = $f->saveFile();
        if(!is_array($file)){return $data['code']=$file;}

        //生成缩略图
        $img_m = $f->thumb($file,'m',279,279);
        $img_s = $f->thumb($file,'s',144,144);

        //图片库关联
        $this->isNewRecord = true;
        $this->model     = 'avatar';
        $this->project_id= 0;
        $this->rel_id    = $user_id;
        $this->created_by= $_POST['created_by'];
        $this->o_path    = $file['path'].$file['file'];
        $this->m_path    = $img_m;
        $this->s_path    = $img_s;
        $this->save();

        //更新用户avatar属性
        if(isset($_POST['type']) && $_POST['type']=='update'){
            $user = User::findOne(['user_id'=>$user_id]);
            $user->avatar = 1;
            $user->save();
        }

        //日志
        $log = new Log();
        $log->addLog(0,0,$user_id,'user','img-upload','更新头像',$this->created_by);

        //返回客户端数据
        $data['code']    = 10000;
        $data['images']  = $this;

        return $data;
    }
    
    
    public function remove($model_name='item',$id,$user_id)
    {
        //验证方法
        if(!Yii::$app->request->isDelete)
        {
            $data['code'] = 400;
            return $data;
        }else{
            $params = Yii::$app->request->bodyParams;
        }

        //检查参数是否正确
        if(isset($params['project_id'],$id,$params['rel_id'],$model_name,$params['updated_by'])){
            $this->rel_id = $params['rel_id'];
            $query = $this->findOne(['model'=>$model_name,'img_id'=>$id,'project_id'=>$params['project_id'],'rel_id'=>$this->rel_id]);
            //检查记录是否存在
            if(empty($query)){
                $data['code']  = 50001;
                return $data;
            }
        }else{
            $data['code'] = 20000;
            return $data;
        }

        //删除文件
        self::removeFile($query);
        $query->delete();

        //更新关联记录
        if($model_name == 'item'){
            $model = Item::findOne(['item_id'=>$this->rel_id]);
            $model->updated_by = $params['updated_by'];
            $model->save();
            $parent_id = $model->box_id;
            $rel_id = $model->item_id;
        }else{
            $model = Box::findOne(['box_id'=>$this->rel_id]);
            $model->updated_by = $params['updated_by'];
            $model->save();
            $parent_id = $model->project_id;
            $rel_id = $model->box_id;
        }

        //日志
        $log = new Log();
        $log->addLog($parent_id,$rel_id,$user_id,$model_name,'img-delete','删除图片',$params['updated_by']);

        $data['code'] = 10000;
        return $data;

    }

    public static function removeImg($id)
    {
        $db = self::findOne($id);
        self::removeFile($db);
        $db->delete();
    }

    public static function removeAll($project_id)
    {
        $imgs = self::findAll(['project_id'=>$project_id]);
        foreach ($imgs as $db){
            self::removeFile($db);
        }
        self::deleteAll(['project_id'=>$project_id]);
    }

    protected static function removeFile($db)
    {
        $path = Yii::$app->params['UploadPath'];
        if($db->o_path!="" && file_exists($path.$db->o_path)){unlink($path.$db->o_path);}
        if($db->l_path!="" && file_exists($path.$db->l_path)){unlink($path.$db->l_path);}
        if($db->m_path!="" && file_exists($path.$db->m_path)){unlink($path.$db->m_path);}
        if($db->s_path!="" && file_exists($path.$db->s_path)){unlink($path.$db->s_path);}
        return;
    }
    
    

}
