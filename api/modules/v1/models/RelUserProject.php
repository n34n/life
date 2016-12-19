<?php

namespace api\modules\v1\models;

use Yii;
use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;
use api\models\User;

/**
 * This is the model class for table "rel_user_project".
 *
 * @property integer $user_id
 * @property integer $project_id
 * @property integer $is_manager
 * @property integer $is_default
 * @property User $user
 */
class RelUserProject extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'rel_user_project';
    }

    public function fields()
    {
        $fields = parent::fields();

        $extra = array('get-token','set-default');

        $ctl = Yii::$app->controller->id;
        $act = Yii::$app->requestedAction->id;

        if(!in_array($act,$extra)){
            $fields['user'] = 'user';
        }

        if($ctl == "project" && $act="view"){
            unset($fields);
            $fields['user_id']    = 'user_id';
            $fields['is_manager'] = 'is_manager';
            $fields['nickname']   = function(){
                $data = (empty($this->user))?'':$this->user->nickname;
                return $data;
            };
            $fields['avatar']   = function(){
                $data = (empty($this->user->img))?'':Yii::$app->params['imgServer'].$this->user->img->s_path;
                return $data;
            };
            $fields['join_at'] = 'join_at';
            //$fields['avatar']     = $this->user->img->s_path;
        }

        return $fields;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'project_id'], 'required'],
            [['user_id', 'project_id', 'is_manager', 'is_default'], 'integer'],
            //[['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'user_id']],
        ];
    }

    //检查用户是否有权限操作项目
    public static function checkUserHasProject($uid,$pid)
    {
        $model = self::findOne(['user_id'=>$uid, 'project_id'=>$pid]);
        if(!empty($model)){
            return 10000;
        }else{
            return 10111;
        }
    }

    public static function getUserList($pid)
    {
        $query = self::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => Yii::$app->params['pageSize'],
            ],
        ]);

        $query->where(['project_id' => $pid]);

        $query->orderBy("join_at");

        return $dataProvider;
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['user_id' => 'user_id']);
    }

    public function getProject()
    {
        return $this->hasOne(Project::className(), ['project_id' => 'project_id']);
    }
}
