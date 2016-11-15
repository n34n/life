<?php

namespace api\modules\v1\models;

use Yii;
use yii\db\ActiveRecord;
use common\components\Upload;

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
            [['rel_id'], 'integer'],
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


    public function upload($model_name='item')
    {
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
        $this->o_path    = $file['path'].$file['file'];
        $this->l_path    = $img_l;
        $this->m_path    = $img_m;
        $this->s_path    = $img_s;
        $this->save();

        //返回客户端数据
        $data['code']      = 10000;
        $data['images']  = $this;

        return $data;
    }

}
