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
            'tag_data',
            'nickname_updated',
            'avatar_updated',
            'created_at',
            'updated_at',
            'img'=>function(){
                        $data = (empty($this->img))?'':$this->img;
                        return $data;
                    },
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
        $this->nickname = $_POST['created_by'];
        $this->tag_data   = trim(Yii::$app->params['defaultTags']);
        $this->save();
        $user_id = $this->getId();

        //创建用户账户
        $ua   = new UserAccount();
        $data['account'] = $ua->create($user_id);
        $data['account']->nickname = $this->nickname;

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
            $nickname= $userinfo->nickname;
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
            if($model->nickname_updated == 0){
                $data = $this->updateNickname($user_id,$nickname,$params,$model);
            }
        }

        //更新标签
        if(isset($params['tags'])){
            $data = $this->updateTags($userinfo,$params,$model);
        }

        $data['code'] = 10000;
        return $data;
    }


    //更新名称
    protected function updateNickname($user_id,$nickname,$params,$model)
    {
        //$nickname = $this->getNickname($model);
        $model->nickname = $params['nickname'];
        if(isset($params['type']) && $params['type']=='update') {
            $model->nickname_updated = 1;
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

    //更新名称
    public static function updateNicknameByLogin($user_id,$nickname)
    {
        //$nickname = $this->getNickname($model);
        $model = self::findOne(['user_id'=>$user_id]);
        if($model->nickname == $nickname){
            return;
        }
        $_nickname = $model->nickname;

        if($model->nickname_updated == 0){
            $model->nickname = $nickname;
        }
        $model->user_id = $user_id;
        $model->save();

        //日志
        $log = new Log();
        $message = '昵称['.$_nickname.'->'.$nickname.']';
        $log->addLog($user_id,0,$user_id,'user','update',$message,$_nickname);

        $data['code']    = 10000;
        return $data;
    }

    //更新标签
    protected function updateTags($userinfo,$params,$model)
    {
        $model->user_id = $user_id = $userinfo->user_id;
        $model->tag_data= $params['tags'];
        $model->save();

        //日志
        $log = new Log();
        $message = '标签更新';
        $log->addLog($user_id,0,$user_id,'user','update',$message,$userinfo->nickname);

        $data['code']    = 10000;
        return $data;
    }


    //获取用户昵称
    protected function getNickname($model)
    {
        return $model->nickname;
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
        //UserAccount::find(['access_token' => $token])->createCommand()
        //$ua = new UserAccount();
        $cache = yii::$app->cache;

        $data = $cache->get($token);

        if ($data === false) {
            // $data 在缓存中没有找到，则重新计算它的值
            $data = UserAccount::findOne(['access_token' => $token]);

            // 将 $data 存放到缓存供下次使用
            $cache->set($token, $data, 3600);
        }

        //$data = UserAccount::findOne(['access_token' => $token]);
        return $data;
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
