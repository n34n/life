<?php

namespace api\modules\v1\models;

use api\models\User;
use Yii;
use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;
use api\modules\v1\models\RelUserProject;
use api\modules\v1\models\Log;

use api\modules\v1\models\Box;
use api\modules\v1\models\Item;
use api\modules\v1\models\Images;


/**
 * This is the model class for table "project".
 *
 * @property integer $project_id
 * @property string $name
 * @property integer $owner_id
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
        $fields = parent::fields();

        unset($fields['updated_at'],$fields['updated_by']);

        $extra = array('index');

        $ctl = Yii::$app->controller->id;
        $act = Yii::$app->requestedAction->id;

        if(in_array($act,$extra)){
            $fields['owner'] = 'owner';
        }

        if($ctl == "project" && $act=="view"){
            $fields['member'] = 'member';
        }

        return $fields;
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

        $query->orderBy("project_id ASC");

        return $dataProvider;
    }


    //创建项目
    public function create($user_id,$nickname,$type=1,$is_default=0)
    {
        if(!isset($user_id,$_POST['name'],$type,$nickname)) {
            $data['code'] = 20000;
            return $data;
        }

        $this->name = $_POST['name'];
        $this->type = $type;
        $this->owner_id = $user_id;
        $this->created_at = time();
        $this->created_by = $nickname;
        $this->updated_by = $nickname;

        if($this->save()){
            $rel = new RelUserProject();
            $rel->user_id = $user_id;
            $rel->project_id = $this->getPrimaryKey();
            $rel->is_manager = 1;
            $rel->is_default = $is_default;
            if($rel->save()){
                $data['code'] = 10000;
                $data['data']['project_id'] = $rel->project_id;
                $data['data']['name']       = $this->name;
                $data['data']['type']       = $type;
                $data['data']['created_at'] = $this->created_at;
                $data['data']['created_by'] = $nickname;

            }else{
                $data['code'] = 10002;
            }
        }else{
            $data['code'] = 10001;
        }

        //日志
        if($data['code'] == 10000){
            $log = new Log();
            $type = ($this->type==1)?"成功添加个人项目":"成功添加共享项目";
            $message = $type."[".$this->name."]";
            $log->addLog($this->project_id,$user_id,$user_id,'project','create',$message,$nickname);
        }

        return $data;

    }


    //新建用户时,创建默认项目
    public function createDefault($user_id,$nickname)
    {
        $_POST['name'] = Yii::$app->params['defaultProject'];
        $data = $this->create($user_id,$nickname,1,1);
        return $data['data'];
    }


    //更新项目
    public function updateInfo($user_id,$nickname,$id)
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
        if(!isset($user_id,$nickname,$id) || !(isset($params['name']) || isset($params['type']))){
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
        $rel = RelUserProject::findOne(['project_id'=>$id,'user_id'=>$user_id]);
        if($rel->is_manager != 1){
            $data['code'] = 10111;
            return $data;
        }

        //更新名称
        if(isset($params['name'])){
            $data = $this->updateName($user_id,$nickname,$id,$params,$model);
            if($data['code'] != 10000){
                return $data;
            }
        }

        //更新类型
        if(isset($params['type'])){
            $data = $this->updateType($user_id,$nickname,$id,$params,$model);
            if($data['code'] != 10000){
                return $data;
            }
        }

        $data['code'] = 10000;
        return $data;
    }


    //更新名称
    protected function updateName($user_id,$nickname,$id,$params,$model)
    {
        if($params['name'] == $model->name){
            $data['code'] = 50100;
            return $data;
        }else{
            $message = '项目['.$model->name.']名称修改为['.$params['name'].']';
        }

        $model->project_id = $id;
        $model->name = $params['name'];
        $model->updated_by = $nickname;
        $model->save();

        //日志
        $log = new Log();
        $log->addLog($id,$user_id,$user_id,'project','update-name',$message,$nickname);

        $data['code']    = 10000;
        return $data;
    }


    //更新项目类型
    protected function updateType($user_id,$nickname,$id,$params,$model)
    {
        if($params['type'] == $model->type){
            $data['code'] = 50100;
            return $data;
        }

        switch ($params['type'])
        {
            case 1:
                /*
                 * 将其他用户踢出项目
                 * 客户端:在执行更新类型时,应告知用户,多人转单人时将会把其他用户踢出该项目后,才能转成单人项目
                 */
                //删除项目成员
                $relusers = RelUserProject::find()->where(['project_id'=>$id])->andWhere(['<>', 'user_id', $user_id])->all();
                foreach ($relusers as $reluser){
                    $log = new Log();
                    $_user = User::findOne($reluser->user_id);
                    $message  = "项目[".$model->name."],注销成员".$_user->nickname;
                    $log->addLog($id,$user_id,$reluser->user_id,'project','member-delete',$message,$nickname);
                }


                RelUserProject::deleteAll(['project_id'=>$id,'is_manager'=>0]);
                $message = "项目[".$model->name."]从共享项目转成个人项目";
                break;
            case 2:
                $message = "项目[".$model->name."]从个人项目转成共享项目";
                break;
            default:
                $data['code'] = 50100;
                return $data;
                break;
        }

        //更新项目类型
        $model->project_id = $id;
        $model->type = $params['type'];
        $model->updated_by = $nickname;
        $model->save();

        //日志
        $log = new Log();
        $log->addLog($id,$user_id,$user_id,'project','update-type',$message,$nickname);

        $data['code']    = 10000;
        return $data;
    }


    //设置默认项目
    public function setDefault($user_id,$project_id)
    {
        //检查参数
        if(!isset($user_id,$project_id)){
            $data['code']  = 20000;
            return $data;
        }

        //检查用户是否有操作当前项目权限
        $rel = RelUserProject::findOne(['user_id'=>$user_id,'project_id'=>$project_id]);
        if(!empty($rel)){
            Yii::$app->db->createCommand()
                ->update('rel_user_project', ['is_default' => 1], ['user_id'=>$user_id,'project_id'=>$project_id])
                ->execute();
//            $rel->is_default = 1;
//            $rel->save();
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
        return $data['data'];
    }


    //加入项目
    public function join($user)
    {
        //检查参数
        if(!isset($user->user_id,$_POST['project_id'])){
            $data['code']  = 20000;
            return $data;
        }

        if($user->user_id == $_POST['manager_id']){
            $data['code']  = 405;
            return $data;
        }

        //验证资源是否存在
        $data['code'] = RelUserProject::checkUserHasProject($_POST['manager_id'],$_POST['project_id']);
        if($data['code'] != 10000) {return $data;}

        //验证用户是否已经添加过
        $data['code'] = RelUserProject::checkUserHasProject($user->user_id,$_POST['project_id']);
        if($data['code'] == 10000) {$data['code']=10112;return $data;}

        //关联项目
        $rel = new RelUserProject();
        $rel->user_id = $user->user_id;
        $rel->project_id = $_POST['project_id'];
        $rel->is_manager = 0;
        $rel->save();

        //用户数据
        $projinfo = self::findOne($_POST['project_id']);
        $nickname = $user->nickname;
        $message  = "成功加入项目[".$projinfo->name."]";

        //日志
        $log = new Log();
        $log->addLog($_POST['project_id'],$_POST['manager_id'],$user->user_id,'project','member-join',$message,$nickname);

        $data['code']    = 10000;
        return $data;
    }


    //删除成员
    public function deleteMember($user)
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
        if(!isset($user->user_id,$params['project_id'],$params['user_id'])){
            $data['code']  = 20000;
            return $data;
        }

        //检查用户是否是管理员
        $one = RelUserProject::findOne(['user_id'=>$user->user_id,'project_id'=>$params['project_id']]);
        if(empty($one)){
            //数据是否存在
            $data['code'] = 405;
            return $data;
        }else{
            //是否是管理员
            if($one->is_manager!=1){
                $data['code'] = 10111;
                return $data;
            }
        }

        //自己不能删除自己
        if($user->user_id == $params['user_id']){
            $data['code']  = 405;
            return $data;
        }

        //踢出成员
        $rel = RelUserProject::findOne(['user_id'=>$params['user_id'],'project_id'=>$params['project_id']]);
        if(empty($rel)){
            $data['code'] = 50001;
            return $data;
        }
        RelUserProject::deleteAll(['user_id'=>$params['user_id'],'project_id'=>$params['project_id']]);

        //用户数据
        $projinfo = self::findOne($params['project_id']);
        $userinfo = User::findOne(['user_id'=>$params['user_id']]);
        $nickname = $user->nickname;
        $message  = "项目[".$projinfo->name."],注销成员".$userinfo->nickname;

        //print_r($user);

        //日志
        $log = new Log();
        $log->addLog($params['project_id'],$user->user_id,$params['user_id'],'project','member-delete',$message,$nickname);

        $data['code']    = 10000;
        return $data;
    }


    //删除项目
    public function remove($user,$id)
    {
        //验证方法
        if(!Yii::$app->request->isDelete)
        {
            $data['code'] = 400;
            return $data;
        }

        //检查参数
        if(!isset($user->user_id,$user->nickname,$id)){
            $data['code']  = 20000;
            return $data;
        }

        //检查资源是否存在
        $info = self::findOne($id);
        $proj = RelUserProject::findOne(['project_id'=>$id,'user_id'=>$user->user_id]);
        if(empty($proj)){
            $data['code']  = 50001;
            return $data;
        }else{
            //检查是否有项目权限
            if($proj->is_manager!=1){
                $data['code']  = 10000;
                //创建日志
                $reluser = RelUserProject::findOne(['project_id'=>$id,'is_manager'=>1]);
                $log = new Log();
                $message = "退出项目[#".$id." ".$info->name."]";
                $log->addLog($id,$reluser->user_id,$user->user_id,'project','member-left',$message,$user->nickname);
                return $data;
            }
            $obj = self::findOne($id);
            $name = $obj->name;
        }

        //删除图片
        Images::removeAll($id);

        //删除物品
        Item::removeAll($id);

        //删除盒子
        Box::deleteAll(['project_id'=>$id]);

        //删除项目成员
        $relusers = RelUserProject::find()->where(['project_id'=>$id])->all();
        foreach ($relusers as $reluser){
            $log = new Log();
            $_user = User::findOne($reluser->user_id);
            $message  = "项目[".$info->name."],注销成员".$_user->nickname;
            $log->addLog($id,$user->user_id,$reluser->user_id,'project','member-delete',$message,$user->nickname);
        }
        RelUserProject::deleteAll(['project_id'=>$id]);

        //删除项目
        $obj->delete();

        //创建日志
        $log = new Log();
        $message = "删除项目[".$name."]";
        $log->addLog($id,$user->user_id,$user->user_id,'project','delete',$message,$user->nickname);

        $data['code'] = 10000;
        return $data;
    }

    //生成链接
   public function getMember()
    {
        return $this->hasMany(RelUserProject::className(), ['project_id' => 'project_id'])->orderBy('join_at');
    }


    //扩展字段
    public function getRel()
    {
        return $this->hasOne(RelUserProject::className(), ['project_id' => 'project_id'])->where(['user_id'=>$_GET['user_id']]);
    }

    //扩展字段
    public function getOwner()
    {
        //return $_GET['user_id'];
        return $this->hasOne(User::className(), ['user_id' => 'owner_id']);
    }
}
