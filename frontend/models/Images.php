<?php

namespace frontend\models;

use Yii;
use common\components\Upload;

/**
 * This is the model class for table "image".
 *
 * @property integer $img_id
 * @property integer $project_id
 * @property string $model
 * @property integer $rel_id
 * @property string $o_path
 * @property string $l_path
 * @property string $m_path
 * @property string $s_path
 * @property integer $created_at
 * @property string $created_by
 */
class Images extends \yii\db\ActiveRecord
{
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
            [['project_id', 'rel_id', 'created_at'], 'integer'],
            [['model'], 'required'],
            [['model'], 'string', 'max' => 20],
            [['o_path', 'l_path', 'm_path', 's_path'], 'string', 'max' => 128],
            [['created_by'], 'string', 'max' => 45],
        ];
    }


    public function setAvatar($user_id,$nickname,$avatar_url)
    {
        //验证参数
        if(!isset($user_id,$nickname,$avatar_url) && $avatar_url!=''){
            $data['code']  = 20000;
            return $data;
        }
        
        //保存原图
        $f = new Upload();
        $file = $f->getFileAndSave($avatar_url);
        if(!is_array($file)){
            $data['code']=$file;
            return $data;
        }

        //生成缩略图
        $img_m = $f->thumb($file,'m',279,279);
        $img_s = $f->thumb($file,'s',144,144);

        //图片库关联
        $this->isNewRecord = true;
        $this->model     = 'avatar';
        $this->project_id= 0;
        $this->rel_id    = $user_id;
        $this->created_by= $nickname;
        $this->o_path    = $file['path'].$file['file'];
        $this->l_path    = $avatar_url;//保存微信头像URL
        $this->m_path    = $img_m;
        $this->s_path    = $img_s;
        $this->save();

        //返回客户端数据
        $data['code']    = 10000;
        $data['images']  = $this;

        return $data;
    }
}
