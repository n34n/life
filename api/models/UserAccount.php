<?php
namespace api\models;
use api\modules\v1\models\Project;
use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use api\models\User;
use api\modules\v1\models\Images;

class UserAccount extends ActiveRecord implements IdentityInterface {
    /**
     * @inheritdoc
     */

    public static function tableName()
    {
        return 'user_account';
    }


    public function fields()
    {
        return [
            'user_id',
            'access_token',
            'account',
            'device',
            'type',
            'info',
        ];
    }
    /**
     * Finds an identity by the given ID.
     * @param string|integer $id the ID to be looked for
     * @return IdentityInterface the identity object that matches the given ID.
     * Null should be returned if such an identity cannot be found
     * or the identity is not in an active state (disabled, deleted, etc.)
     */
    public static function findIdentity($id)
    {
        // TODO: Implement findIdentity() method.
        return static::findOne(['user_id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    //检查用户是否存在
    public static function checkUserExsit()
    {
        if(!isset($_POST['account'],$_POST['type'])){
            return $data['code'] = 20000;
        }

        $model      = static::findOne(['account'=>$_POST['account'], 'type' =>$_POST['type']]);
        if(!$model){
            $data['code'] = 40000;
        }else{
            $data['code'] = 10000;
            $data['user'] = $model;
        }
        return $data;
    }

    //检查用户账户是否存在
    public static function checkUserAccountExsit()
    {
        if(!isset($_POST['account'],$_POST['device'],$_POST['type'])){
            return $data['code'] = 20000;
        }

        $model = static::findOne([
            'account'=>$_POST['account'],
            'device' =>$_POST['device'],
            'type'=>$_POST['type']]);
        if(!$model){
            $data['code'] = 40000;
        }else{
            $data['code'] = 10000;
            $data['user'] = $model;
        }
        return $data;
    }

    //创建用户账户
    public function create($id)
    {
        $this->user_id = $id;
        $this->access_token = $this->setToken();
        $this->account = $_POST['account'];
        $this->device  = $_POST['device'];
        $this->type    = $_POST['type'];
        $this->save();
        return $this;
    }

    /**
     * Finds an identity by the given token.
     * @param mixed $token the token to be looked for
     * @param mixed $type the type of the token. The value of this parameter depends on the implementation.
     * For example, [[\yii\filters\auth\HttpBearerAuth]] will set this parameter to be `yii\filters\auth\HttpBearerAuth`.
     * @return IdentityInterface the identity object that matches the given token.
     * Null should be returned if such an identity cannot be found
     * or the identity is not in an active state (disabled, deleted, etc.)
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        // TODO: Implement findIdentityByAccessToken() method.
    }

    /**
     * Returns an ID that can uniquely identify a user identity.
     * @return string|integer an ID that uniquely identifies a user identity.
     */
    public function getId()
    {
        // TODO: Implement getId() method.
    }

    /**
     * Returns a key that can be used to check the validity of a given identity ID.
     *
     * The key should be unique for each individual user, and should be persistent
     * so that it can be used to check the validity of the user identity.
     *
     * The space of such keys should be big enough to defeat potential identity attacks.
     *
     * This is required if [[User::enableAutoLogin]] is enabled.
     * @return string a key that is used to check the validity of a given identity ID.
     * @see validateAuthKey()
     */
    public function getAuthKey()
    {
        // TODO: Implement getAuthKey() method.
    }


    /**
     * Validates the given auth key.
     *
     * This is required if [[User::enableAutoLogin]] is enabled.
     * @param string $authKey the given auth key
     * @return boolean whether the given auth key is valid.
     * @see getAuthKey()
     */
    public function validateAuthKey($authKey)
    {
        // TODO: Implement validateAuthKey() method.
    }

    //获取key
    public static function getSecret()
    {
        return Yii::$app->params['secret'];
    }


    //检查用户是否登录
    public static function checkAccess()
    {
        //判断是否接收到用户登录类型
        if(isset($_POST['user_id'],$_POST['account'],$_POST['device'],$_POST['type']))
        {
            $conditions['user_id'] = $_POST['user_id'];
            $conditions['account'] = $_POST['account'];
            $conditions['device']  = $_POST['device'];
            $conditions['type']    = $_POST['type'];

            $user   = self::find()->where($conditions)->one();

            //判断是否查到用户
            if(empty($user))
            {
                //用户不存在,返回登录界面
                $data['code']     = 40000;
            }else{
                //判断用户登录状态
                if($user->status == 10)
                {
                    //设置token令牌,用于后续页面用户验证使用
                    $user->access_token = self::setToken();
                    $user->save();

                    //状态为10,进入主界面
                    $data['code']           = 10000;
                    $data['img']   = $user->access_token;
                    $data['access_token']   = $user->access_token;

                    $proj = new Project();
                    $_proj= $proj->getDefault($user->user_id);
                    $data['project'] = $_proj['data'];

                }else{
                    //状态为0,返回登录界面
                    $data['code']     = 40001;
                }
            }
        }else{
            //未提交必要参数,返回登录界面
            $data['code']     = 20000;
        }

        return $data;
    }


    //生成Token
    public static function setToken()
    {
        $secret = self::getSecret();
        $str = '';
        ksort($_POST);
        foreach ($_POST as $value){
            $str .= $value;
        }
        $str .= time().$secret;
        return md5($str);
    }


    //检查签名
    public static function checkSign()
    {
        //验证参数
        if(!isset($_POST['account'],$_POST['created_by'],
            $_POST['device'],$_POST['type'],
            $_POST['timestamp'],$_POST['sign'])) {
            return $data['code']  = 20000;
        }

        //制作签名
        $secret = self::getSecret();
        $str  = '';
        $sign = $_POST['sign'];
        unset($_POST['sign']);
        ksort($_POST);
        foreach ($_POST as $value){$str .= $value;}
        $_sign = md5($str.$secret);
        //print_r($_POST);
        //echo $_sign;

        //验证签名
        $data['code'] = ($sign == $_sign)?10000:30000;
        return $data;
    }


    //获取用户信息
    public function getInfo()
    {
        return $this->hasOne(User::className(), ['user_id' => 'user_id']);
    }



}
