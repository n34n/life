<?php
namespace api\models;
use api\modules\v1\models\Images;
use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use api\models\UserAccount;
use api\modules\v1\models\Log;

class User extends ActiveRecord implements IdentityInterface {
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user';
    }

    public function fields()
    {
        return [
            'user_id',
            'nickname',
            '_nickname',
            'avatar',
            'tag_data',
            'created_at',
            'updated_at',
            'img',
        ];
    }

    public static function getUserInfo($token)
    {
        $query = self::find()->from(['u'=>'user'])
            ->leftJoin(['a'=>'user_account'],'u.user_id = a.user_id')
            ->where(['a.access_token'=>$token])
            ->one();
        return $query;
    }

    public function create()
    {
        //创建用户
        $this->created_at = time();
        $this->_nickname = $_POST['created_by'];
        $this->tag_data   = trim(Yii::$app->params['defaultTags']);
        $this->save();
        $user_id = $this->getId();

        //创建用户账户
        $ua   = new UserAccount();
        $data['account'] = $ua->create($user_id);

        return $data;
    }


    public function updateInfo($userinfo)
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
        if(isset($params['nickname']) || isset($params['tags'])) {
            $user_id = $userinfo->user_id;
        }else{
            $data['code'] = 20000;
            return $data;
        }

        //检查数据是否存在
        $model = $this->findOne(['user_id'=>$user_id]);
        if(!$model){
            $data['code'] = 50000;
            return $data;
        }

        //更新昵称
        if(isset($params['nickname'])){
            $data = $this->updateNickname($user_id,$params,$model);
        }

        //更新标签
        if(isset($params['tags'])){
            $data = $this->updateTags($userinfo,$params,$model);
        }

        $data['code'] = 10000;
        return $data;
    }


    //更新名称
    protected function updateNickname($user_id,$params,$model)
    {
        $nickname = $this->getNickname($model);
        if(isset($params['type']) && $params['type']=='update') {
            $model->nickname = $params['nickname'];
        }else{
            $model->_nickname = $params['nickname'];
        }

        $model->user_id = $user_id;
        $model->save();

        //日志
        $log = new Log();
        $message = '名称['.$nickname.'->'.$params['nickname'].']';
        $log->addLog($user_id,0,$user_id,'user','update',$message,$nickname);

        $data['code']    = 10000;
        return $data;
    }

    //更新标签
    protected function updateTags($userinfo,$params,$model)
    {
        $nickname = $this->getNickname($userinfo);
        $model->user_id = $user_id = $userinfo->user_id;
        $model->tag_data= $params['tags'];
        $model->save();

        //日志
        $log = new Log();
        $message = '标签更新';
        $log->addLog($user_id,0,$user_id,'user','update',$message,$nickname);

        $data['code']    = 10000;
        return $data;
    }


    //获取用户昵称
    protected function getNickname($model)
    {
        return $nickname = ($model->nickname!="")?$model->nickname:$model->_nickname;
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        $userinfo = $_POST['userinfo'];
        return $userinfo;
        //return static::findOne(['user_id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return UserAccount::findOne(['access_token' => $token]);
    }

    //这个就是我们进行yii\filters\auth\QueryParamAuth调用认证的函数，下面会说到。
    public function loginByAccessToken($token, $type) {
        //查询数据库中有没有存在这个token
        return static::findIdentityByAccessToken($token, $type);
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }


    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }


    //获取图片
    public function getImg()
    {
        return $this->hasOne(Images::className(), ['rel_id' => 'user_id'])->where(['model'=>'avatar']);
    }
}
